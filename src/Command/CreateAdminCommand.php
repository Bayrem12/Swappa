<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Add a short description for your command',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        
    }

    // src/Command/CreateAdminCommand.php
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $user = new User();
            $user->setEmail('admin@swappa.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $output->writeln('Admin user created!');
            return Command::SUCCESS;
        }
}
