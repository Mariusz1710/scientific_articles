<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }


    /** @return User[] */
    public function findReviewers(): array
    {
         $users = $this->findBy([], ['fullName' => 'ASC']);

         return array_values(array_filter(
         $users,
         static fn (User $user) => in_array('ROLE_REVIEWER', $user->getRoles(), true)
    ));
}
   
}
