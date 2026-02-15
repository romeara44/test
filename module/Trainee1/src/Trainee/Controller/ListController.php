<?php
// Filename: /module/Trainee/src/Trainee/Controller/ListController.php
namespace Trainee\Controller;

use Trainee\Service\PostServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ListController extends AbstractActionController
{
    /**
     * @var \Trainee\Service\PostServiceInterface
    */
    protected $postService;

    public function __construct(PostServiceInterface $postService)
    {
        $this->postService = $postService;
    }

    public function indexAction()
    {
        $test = $this->postService->findAllPosts();
        return new ViewModel(array(
            'posts' => $this->postService->findAllPosts()
        ));
    }

    public function detailAction()
    {
        $id = $this->params()->fromRoute('id');

        try {
            $post = $this->postService->findPost($id);
        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('trainee');
        }

        return new ViewModel(array(
            'post' => $post
        ));
    }
}