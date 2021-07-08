<?php


namespace App\MessageHandler\Command;


use App\Entity\Movie;
use App\Entity\User;
use App\Entity\UserLike;
use App\Message\Command\UserLikeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserLikeHandler implements MessageHandlerInterface
{

    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $messengerAuditLogger)
    {

        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $messengerAuditLogger;
    }

    public function __invoke(UserLikeMessage $userLikeMessage)
    {
        if(null == $userLikeMessage->getUserId()) {
            $this->logger->alert(sprintf('User id cannot be null!'));
            return;
        }

        if(null == $userLikeMessage->getMovieId()) {
            $this->logger->alert(sprintf('Movie id cannot be null!'));
            return;
        }

        $movie = $this->entityManager->getRepository(Movie::class)->find($userLikeMessage->getMovieId());

        if($movie instanceof Movie === false) {
            $this->logger->alert(sprintf('Movie by id %e not found!', $userLikeMessage->getMovieId()));
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->find($userLikeMessage->getUserId());

        if($user instanceof User === false) {
            $this->logger->alert(sprintf('Movie by id %e not found!', $userLikeMessage->getMovieId()));
            return;
        }

        $userLike = $this->entityManager->getRepository(UserLike::class)->findUserLike($userLikeMessage);

        if($userLike instanceof UserLike) {

            $this->entityManager->remove($userLike);
        } else{
            $userLike = new UserLike();
            $userLike->setCreatedAt(new \DateTime('now'));
            $userLike->setMovie($movie);
            $userLike->setUser($user);

            $errors = $this->validator->validate($userLike);

            if(count($errors) > 0) {
                $this->logger->alert(sprintf('User by email %e have a validation error !', $user->getEmail()));
                return;
            }

            $this->entityManager->persist($userLike);
        }

        try {
            $this->entityManager->flush();
        }catch (\Exception $exception) {
            $this->logger->alert(sprintf('User by email %e cannot be save !', $exception->getMessage()));
            return;
        }
    }
}