<?php
// Filename: /module/Trainee/src/Trainee/Service/PostService.php
namespace Trainee\Service;

use Trainee\Mapper\PostMapperInterface;
//use Trainee\Model\PostInterface;
use Trainee\Entity\TraineeUserInterface;

class PostService implements PostServiceInterface
{
    /**
     * @var \Trainee\Mapper\PostMapperInterface
    */
    protected $postMapper;

    /**
     * @param PostMapperInterface $postMapper
    */
    public function __construct(PostMapperInterface $postMapper)
    {
        $this->postMapper = $postMapper;
    }

    /**
     * {@inheritDoc}
    */
    public function findAllPosts()
    {
        $identity = $this->getIdentity();
        //$clientObj = $this->getServiceLocator()->get('Client\Model\CompanyTable')->getClientCompany($identity['u_company_id']);
        $stuff = $this->postMapper->getCompany($identity['u_company_id']);
        $paginationDetails = $this->GetUserPaginationInformationByCompany($clientObj->c_name);
        $litmosUsers = $this->GetUsersByCompany($clientObj->c_name, $paginationDetails['Pagination']['TotalCount']);
        
        return $this->postMapper->findAll();
    }

    /**
     * {@inheritDoc}
    */
    public function findPost($id)
    {
        return $this->postMapper->find($id);
    }

    /**
     * {@inheritDoc}
    */
    public function savePost(TraineeUserInterface $post)
    {
        return $this->postMapper->save($post);
    }

    /**
     * {@inheritDoc}
    */
    public function deletePost(TraineeUserInterface $post)
    {
        return $this->postMapper->delete($post);
    }

    public function getIdentity()
    {
        $authService = new \Zend\Authentication\AuthenticationService();
        $authService->setStorage(new \SanAuth\Model\MyAuthStorage('hipaa'));
        $identity = $authService->getIdentity();

        return $identity;
    }
}