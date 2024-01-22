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

    public function __construct(EntityManagerInterface  $em,UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($em);
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() :void
    {
        $this
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ))
            ->setHelp(<<<'EOT'
The <info>fos:user:change-password</info> command changes the password of a user:
  <info>php %command.full_name% matthieu</info>
This interactive shell will first ask you for a password.
You can alternatively specify the password as a second argument:
  <info>php %command.full_name% matthieu mypassword</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        
        
        $user=$this->users->findOneByUsername($username);
        

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('Changed password for user <comment>%s</comment>', $username));
        
      return Command::SUCCESS;
        
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = array();

        if (!$input->getArgument('username')) {
            $question = new Question('Please give the username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $questions['username'] = $question;
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