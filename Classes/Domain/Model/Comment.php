<?php

declare(strict_types = 1);

namespace T3Bootstrap\Blog\Domain\Model;

class Comment extends \T3G\AgencyPack\Blog\Domain\Model\Comment
{
    protected ?FrontendUser $author = null;

    public function getAuthor(): ?FrontendUser
    {
        return $this->author;
    }

    public function setAuthor(?FrontendUser $author): void
    {
        $this->author = $author;
    }
}