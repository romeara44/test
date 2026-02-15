<?php

namespace Trainee\Repository;

use Trainee\Entity\Post;

interface IPostRepository
{
    public function save(Post $post);

}