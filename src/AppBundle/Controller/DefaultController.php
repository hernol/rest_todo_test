<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\Route;

use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc as ApiDoc;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Cache\MemcachedCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\InvalidArgumentException;


use AppBundle\Document\Todo;
use AppBundle\Form\TodoType;
use AppBundle\Repository\TodoRepository;

/**
 * @todo Use DependencyInjection to inject memcached, repositories an entityManagers.
 * Class DefaultController
 * @package AppBundle\Controller
 */
class DefaultController extends FOSRestController {
     
    /**
     * @Rest\Get("/todos/{id}", requirements={"id" = "\w+"})
     * @ApiDoc(
     *  description="Get single Todo record",
     *     statusCodes={
     *          200="Obtain record successfully",
     *          404="Record not found"
     *     }
     * )
     *
     */
    public function getSingleAction(Request $request, $id) {
        $memcached = $this->get('todo.doctrine.cache.memcached');
        $cacheId = 'findById_'.$id;
        if ($memcached->contains($cacheId)) {
            $todo = $memcached->fetch($cacheId);
        } else {
            $todoRepository = $this->get('doctrine_mongodb')->getRepository('AppBundle:Todo');
            $todo = $todoRepository->find($id);
            $memcached->save($cacheId, $todo, 60);
        }

        if($todo) {
            return View::create($todo, Codes::HTTP_OK);
        }

        return View::create(null, Codes::HTTP_NOT_FOUND);
    }
    
    private $limit = 5;
    /**
     * @Rest\Get("/todos")
     * @ApiDoc(
     *  description="Get Todo records",
     *     statusCodes={
     *          200="Obtain records successfully",
     *     }
     * )
     *
     */
    public function getAction(Request $request) {
        $offset = 0;
        if ((int)$request->get('page')) {
            $offset = ((int)$request->get('page')-1)*$this->limit;
        } elseif ((int)$request->get('offset')) {
            $offset = (int)$request->get('offset');
        }
        $memcached = $this->get('todo.doctrine.cache.memcached');
        $cacheId = 'findPaginated_o_'.$offset.'_l_'.$this->limit;
        if ($request->get('filter')) {
            $filterExpArray = explode('|', $request->get('filter'));
            sort($filterExpArray);
            $cacheId .= '_f_'.implode('|', $filterExpArray);
        }
        if ($memcached->contains($cacheId)) {
            $todos = $memcached->fetch($cacheId);
        } else {
            $todoQB = $this->get('doctrine_mongodb')->getRepository('AppBundle:Todo')->createQueryBuilder('t');
            if ($request->get('filter')) {
                $this->addQBFilters($todoQB, $request->get('filter'));
            }
            $todos = [];
            $todoQB->skip($offset)->limit($this->limit);
            $cursor = $todoQB->getQuery()->execute();
            foreach ($cursor as $obj) {
                $todos[] = $obj;
            }        
            $memcached->save($cacheId, $todos, 60);
        }        


        return View::create($todos, Codes::HTTP_OK);
    }
    
    private function addQBFilters($qb, $filterExp) {
        $filterExp = sprintf('%s', $filterExp);
        $filterExpArray = explode("|", $filterExp);
        foreach ($filterExpArray as $filterExp) {
            if (preg_match('/^created_at|updated_at|due_date|completed::(\d{4}-\d{2}-\d{2}|true|false|1|0){1}$/', $filterExp)) {
                list($name, $value) = explode('::', $filterExp);
                $name = sprintf('%s', $name);
                switch($name) {
                    case 'created_at':
                    case 'updated_at':
                    case 'due_date':
                        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
                            throw new InvalidArgumentException('Invalid date format. It must be Y-m-d');
                        }
                        $gte = new \DateTime($value);
                        $lte = new \DateTime($value);
                        $int = new \DateInterval('P1D');
                        $lte->add($int);
                        $qb->field($name)->gte($gte)->lte($lte);
                    break;
                    case 'completed':
                        if ($value === 'true' || $value === '1' || $value === true) {
                            $value = true;
                        } else {
                            $value = false;
                        }
                        $qb->field($name)->equals($value);
                    break;
                    default:
                        throw new InvalidArgumentException('Invalid field name or value');
                    break;
                }
            } else {
                throw new InvalidArgumentException('Invalid field name or value');
            }
        }
    }

    /**
     * @Rest\Post("/todos", name="post_todo")
     * @ApiDoc(
     *     description="Create Todo record",
     *     input={
     *          "class"="AppBundle\Form\TodoType",
     *          "name"=""
     *     },
     *     statusCodes={
     *          201="Created successfully",
     *          400="Invalid form params"
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View;
     *
     */
    public function postAction(Request $request) {
        $response = new Response();

        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo, ['method' => 'POST']);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $todo = $form->getData();
            $todo->setCreatedAt(new \DateTime());
            $todo->setUpdatedAt(new \DateTime());

            $entity = $this->get('doctrine_mongodb')->getManager();
            $entity->persist($todo);
            $entity->flush();
            $putRoute = $this->get('router')->generate('put_todo', array('id'=>$todo->getId()));
            return View::create(null, Codes::HTTP_CREATED, ['Location' => $putRoute]);
        }

        return View::create($form, Codes::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Put("/todos/{id}", name="put_todo", requirements={"id" = "\w+"})
     * @ApiDoc(
     *     description="Update whole  Todo record",
     *     input={
     *          "class"="AppBundle\Form\TodoType",
     *          "name"=""
     *     },
     *     statusCodes={
     *          204="Fields edited successfully",
     *          400="Invalid form params",
     *          404="Record not found"
     *     }
     * )
     *
     */
    public function putAction(Request $request, $id) {
        $todoRepository = $this->get('doctrine_mongodb')->getRepository('AppBundle:Todo');

        $todo = $todoRepository->find($id);

        if($todo) {
            $form = $this->createForm(TodoType::class, $todo, ['method' => 'PUT']);
            if (!$request->request->get('due_date')) {
                $request->request->set('due_date', $todo->getDueDate()->format('Y-m-d H:i:s'));
            }
            $form->submit($request->request->all());

            if ($form->isValid()) {
                $todo = $form->getData();
                $todo->setUpdatedAt(new \DateTime());

                $entity = $this->get('doctrine_mongodb')->getManager();
                $entity->persist($todo);
                $entity->flush();

                return View::create($form, Codes::HTTP_NO_CONTENT);
            }

            return View::create($form, Codes::HTTP_BAD_REQUEST);
        }

        return View::create(null, Codes::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Patch("/todos/{id}", requirements={"id" = "\w+"})
     * @ApiDoc(
     *     description="Update fields Todo record",
     *     input={
     *          "class"="AppBundle\Form\TodoType",
     *          "name"=""
     *     },
     *     statusCodes={
     *          204="Fields edited successfully",
     *          400="Invalid form params",
     *          404="Record not found"
     *     }
     * )
     *
     */
    public function patchAction(Request $request, $id) {
        $todoRepository = $this->get('doctrine_mongodb')->getRepository('AppBundle:Todo');

        $todo = $todoRepository->find($id);

        if($todo) {
            $form = $this->createForm(TodoType::class, $todo, ['method' => 'PATCH']);
            if (!$request->request->get('due_date')) {
                $request->request->set('due_date', $todo->getDueDate()->format('Y-m-d H:i:s'));
            }
            $form->submit($request->request->all(), false);

            if ($form->isValid()) {
                $todo = $form->getData();

                $entity = $this->get('doctrine_mongodb')->getManager();
                $entity->persist($todo);
                $entity->flush();

                return View::create($form, Codes::HTTP_NO_CONTENT);
            }

            return View::create($form, Codes::HTTP_BAD_REQUEST);
        }

        return View::create(null, Codes::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Delete("/todos/{id}", requirements={"id" = "\w+"})
     * @ApiDoc(
     *     description="Remove Todo record",
     *     statusCodes={
     *          204="Removed successfully",
     *          404="Record not found"
     *     }
     * )
     *
     */
    public function deleteAction(Request $request, $id) {
        $todoRepository = $this->get('doctrine_mongodb')->getRepository('AppBundle:Todo');

        $todo = $todoRepository->find($id);

        if(!$todo)
        {
            return View::create(null, Codes::HTTP_NOT_FOUND);
        }

        $entity = $this->get('doctrine_mongodb')->getManager();
        $entity->remove($todo);
        $entity->flush();

        return View::create(null, Codes::HTTP_OK);
    }
}
