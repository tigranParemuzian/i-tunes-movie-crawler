<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Message\Command\UserLikeMessage;
use App\Repository\MovieRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function index(Request $request, MovieRepository $repository): Response
    {
        return $this->render('home/index.html.twig', [
            'trailers'=>$repository->findAll(),
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/trailer/{id}", name="trailer", methods={"GET"})
     */
    public function trailer(Movie $movie, Request $request, MovieRepository $repository): Response
    {
        return $this->render('home/trailer.html.twig', [
            'trailer'=>$movie,
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/like/{id}", name="movie-like")
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     */
    public function like(Movie $movie, MessageBusInterface $messageBus)
    {

        // async version, yes, it's not can bee sync but it's version.
        $message = new UserLikeMessage();
        $message->setMovieId($movie->getId());
        $message->setUserId($this->getUser()->getId());

        $envelop = new Envelope($message,
            []
        );

        $messageBus->dispatch($envelop);

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
