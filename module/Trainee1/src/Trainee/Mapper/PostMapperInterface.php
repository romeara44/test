<?php
// Filename: /module/Trainee/src/Trainee/Mapper/PostMapperInterface.php
namespace Trainee\Mapper;

//use Trainee\Model\PostInterface;
use Trainee\Entity\TraineeUserInterface;

interface PostMapperInterface
{
    /**
     * @param int|string $id
    * @return PostInterface
    * @throws \InvalidArgumentException
    */
    public function find($id);

    /**
     * @return array|PostInterface[]
    */
    public function findAll();

    /**
     * @param PostInterface $postObject
    *
    * @param PostInterface $postObject
    * @return PostInterface
    * @throws \Exception
    */
    public function save(TraineeUserInterface $postObject);

    /**
     * @param PostInterface $postObject
    *
    * @return bool
    * @throws \Exception
    */
    public function delete(TraineeUserInterface $postObject);

    /**
     * @param PostInterface $postObject
    *
    * @return bool
    * @throws \Exception
    */
    public function getCompany($id);
}