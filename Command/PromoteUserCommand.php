<?php

namespace Aldaflux\AldafluxStandardUserCommandBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'suc:user:promote',
    description: 'Promote a user by adding a role',
    aliases: ['user:promote', 'promote']
)]
class PromoteUserCommand extends UserCommand
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Promote a user by adding a role')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'The username, email or ID of the user')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role to grant to the user (e.g. ROLE_ADMIN)')
            ->addOption('replace', null, InputOption::VALUE_NONE, 'If set, replaces all existing roles with the new role instead of adding it')
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command promotes a user by granting them a role:

  <info>php %command.full_name% john ROLE_ADMIN</info>

By default, the role is added to existing roles. To replace all existing roles with the specified role:

  <info>php %command.full_name% john ROLE_ADMIN --replace</info>

If arguments are omitted, the command will prompt for them interactively.
HELP
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        if (!$input->getArgument('identifier')) {
            $question = new Question('Please enter the user identifier (username, email or ID): ');
            $question->setValidator(function ($identifier) {
                if (empty($identifier)) {
                    throw new \RuntimeException('User identifier cannot be empty');
                }
                return $identifier;
            });
            $identifier = $helper->ask($input, $output, $question);
            $input->setArgument('identifier', $identifier);
        }

        if (!$input->getArgument('role')) {
            $question = new Question('Please enter the role to assign (e.g. ROLE_ADMIN): ');
            $question->setValidator(function ($role) {
                if (empty($role)) {
                    throw new \RuntimeException('Role cannot be empty');
                }
                return $role;
            });
            $role = $helper->ask($input, $output, $question);
            $input->setArgument('role', $role);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $identifier = $input->getArgument('identifier');
        $roleInput = $input->getArgument('role');
        $replace = $input->getOption('replace');

        if (!$identifier || !$roleInput) {
            $io->error('Both user identifier and role arguments are required.');
            return Command::FAILURE;
        }

        // Format role to uppercase and prefix with ROLE_ if needed
        $role = strtoupper(trim($roleInput));
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }

        // Find user by ID, email or username
        $user = null;
        if (is_numeric($identifier)) {
            $user = $this->users->find((int) $identifier);
        }

        if (!$user) {
            $user = str_contains($identifier, '@')
                ? $this->users->findOneBy(['email' => $identifier])
                : $this->users->findOneBy(['username' => $identifier]);
        }

        if (!$user && !is_numeric($identifier)) {
            $user = $this->users->find($identifier);
        }

        if (!$user) {
            $io->error(sprintf('User not found with identifier "%s".', $identifier));
            return Command::FAILURE;
        }

        if ($replace) {
            $user->setRoles([$role]);
            $message = sprintf('User "%s" promoted: roles replaced with [%s].', $user, $role);
        } else {
            $existingRoles = $user->getRoles();
            if (!in_array($role, $existingRoles, true)) {
                $existingRoles[] = $role;
            }
            $user->setRoles(array_values(array_unique($existingRoles)));
            $message = sprintf('User "%s" promoted: added role "%s". Current roles: [%s].', $user, $role, implode(', ', $user->getRoles()));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success($message);

        return Command::SUCCESS;
    }
}
