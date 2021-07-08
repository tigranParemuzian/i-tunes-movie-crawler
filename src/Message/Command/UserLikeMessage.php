<?php


namespace App\Message\Command;


class UserLikeMessage
{
    /**
     * @var int
     */
    private $userId;


    /**
     * @var int
     */
    private $movieId;

    public function __construct()
    {
        $this->isLiked = true;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMovieId(): ?int
    {
        return $this->movieId;
    }

    /**
     * @param int $movieId
     */
    public function setMovieId(?int $movieId): self
    {
        $this->movieId = $movieId;
        return $this;
    }
}