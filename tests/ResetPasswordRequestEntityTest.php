<?php

namespace App\Tests;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ResetPasswordRequestEntityTest extends KernelTestCase
{
    use ResetDatabase;

    public function testCreatedAndRemoved(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $resetPasswordRequestRepo = $doctrine->getRepository(ResetPasswordRequest::class);

        self::assertInstanceOf(ResetPasswordRequestRepository::class, $resetPasswordRequestRepo);

        $manager = $doctrine->getManager();

        self::assertCount(0, $manager->getRepository(User::class)->findAll());
        self::assertCount(0, $manager->getRepository(ResetPasswordRequest::class)->findAll());

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('password');
        $doctrine->getManager()->persist($user);
        $doctrine->getManager()->flush();

        self::assertCount(1, $manager->getRepository(User::class)->findAll());

        $request = new ResetPasswordRequest(user: $user, expiresAt: new \DateTimeImmutable(), selector: '1234', hashedToken: '12345');
        $resetPasswordRequestRepo->persistResetPasswordRequest($request);

        self::assertCount(1, $manager->getRepository(ResetPasswordRequest::class)->findAll());

        $resetPasswordRequestRepo->removeResetPasswordRequest($request);

        self::assertCount(0, $manager->getRepository(ResetPasswordRequest::class)->findAll());
    }
}
