<?php

namespace Aldaflux\AldafluxStandardUserCommandBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('suc:user:change-password', 'Change the password of a user')]
class ChangePasswordCommand extends UserCommand
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($em);
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('identifier', InputArgument::REQUIRED, 'The username or email of the user'),
                new InputArgument('password', InputArgument::REQUIRED, 'The new password'),
            ])
            ->setHelp(<<<'EOT'
The <info>suc:user:change-password</info> command changes the password of a user:
  <info>php %command.full_name% matthieu</info>
  <info>php %command.full_name% matthieu@example.com</info>
This interactive shell will first ask you for a password.
You can alternatively specify the password as a second argument:
  <info>php %command.full_name% matthieu mypassword</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');
        $plainPassword = $input->getArgument('password');

        // Vérifie si l'identifier contient un @ pour déterminer s'il s'agit d'un email
        $user = str_contains($identifier, '@')
            ? $this->users->findOneBy(['email' => $identifier])
            : $this->users->findOneBy(['username' => $identifier]);

        if (!$user) {
            $output->writeln('<error>User not found with this username or email.</error>');
            return Command::FAILURE;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('Changed password for user <comment>%s</comment>', $user));

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [];

        if (!$input->getArgument('identifier')) {
            $question = new Question('Please give the username or email:');
            $question->setValidator(function ($identifier) {
                if (empty($identifier)) {
                    throw new \Exception('Identifier (username or email) can not be empty');
                }
                return $identifier;
            });
            $questions['identifier'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter the new password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }
                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
