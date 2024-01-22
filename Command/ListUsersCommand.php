<?php


namespace Aldaflux\AldafluxStandardUserCommandBundle\Command;

use Aldaflux\AldafluxStandardUserCommandBundle\Command\UserCommand;
use Symfony\Component\Console\Command\Command;
use App\Entity\User;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;


use Doctrine\ORM\EntityManagerInterface;

 
 
use Symfony\Component\Console\Attribute\AsCommand;
 

#[AsCommand('suc:user:list', 'Lists all the existing users')]
class ListUsersCommand extends UserCommand
{
 
    public function __construct(EntityManagerInterface  $em, EntityUserProvider $userProviderEntity )
    {
        parent::__construct($em,$userProviderEntity);

    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Lists all the existing users')
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command lists all the users registered in the application:
  <info>php %command.full_name%</info>
By default the command only displays the 50 most recent users. Set the number of
results to display with the <comment>--max-results</comment> option:
  <info>php %command.full_name%</info> <comment>--max-results=2000</comment>
In addition to displaying the user list, you can also send this information to
the email address specified in the <comment>--send-to</comment> option:
  <info>php %command.full_name%</info> <comment>--send-to=fabien@symfony.com</comment>
HELP
            )
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addOption('max-results', null, InputOption::VALUE_OPTIONAL, 'Limits the number of users listed', 50)
        ;
    }

    /**
     * This method is executed after initialize(). It usually contains the logic
     * to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxResults = $input->getOption('max-results');
        
        $allUsers = $this->users->findBy([], ['id' => 'DESC'], $maxResults);
        
        $usersAsPlainArrays = array_map([$this,"UserGetValues"], $allUsers);
        $bufferedOutput = new BufferedOutput();
        $io = new SymfonyStyle($input, $bufferedOutput);
        $io->table(
                $this->getHeaderTable(),
            $usersAsPlainArrays
        );

        $usersAsATable = $bufferedOutput->fetch();
        $output->write($usersAsATable);

        return Command::SUCCESS;
    }
    
    function UserGetValues(User $user)
    {
        $result=array();
        $result[]=$user->getId();
        if($this->hasFullName())
        {   
            $result[]=$user->getFullName();
        }
        if($this->hasUsername())
        {
            $result[]=$user->getUsername();
        }
        if($this->hasEmail())
        {
            $result[]=$user->getEmail();
        }
        $result[]=implode(', ', $user->getRoles());
        return $result;
    }


}