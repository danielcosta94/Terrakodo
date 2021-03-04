<?php

namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskService
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var TaskRepository
     */
    private TaskRepository $taskRepository;

    /**
     * @var RecursiveValidator|ValidatorInterface
     */
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    }

    /**
     * @param Task $task
     *
     * @return ConstraintViolationListInterface
     */
    private function validationTask(Task $task): ConstraintViolationListInterface
    {
        return $this->validator->validate($task);
    }

    /**
     * @param array $data
     * @param Task|null $task
     *
     * @return Task
     */
    private function buildTaskObject(array $data, Task $task = null): Task
    {
        $task ??= new Task();

        if (!empty($data['name'])) {
            $task->setName($data['name']);
        }

        if (!empty($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (!empty($data['priority_level'])) {
            $task->setPriorityLevel($data['priority_level']);
        }

        if (!empty($data['date_completion'])) {
            $task->setDateCompletion(new \DateTime($data['date_completion']));
        }

        return $task;
    }

    public function createTask(array &$request, Task $task = null): array
    {
        $task = $this->buildTaskObject($request, $task);
//        $errors = $this->validateTask($task);


//        if (!count($errors)) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            return [];
//        } else {
//            $errorsList = [];
//
//            foreach ($errorsList as $error) {
//                $errors[] = $error;
//            }
//
//            return $errorsList;
//        }
    }

    public function updateTask(array &$request, int $id)
    {
        $task = $this->taskRepository->find($id);

        if ($task != null) {
            return $this->createTask($request, $task);
        } else {
            throw new \InvalidArgumentException(sprintf("Task with ID: %d not found !!!", $id));
        }
    }

    public function validateTask(int $id)
    {
        $task = $this->taskRepository->find($id);

        if ($task != null) {
            if ($task->getDateCompletion() === null) {
                $task->setDateCompletion(new \DateTime(Carbon::now()->format('Y-m-d')));

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                return [];
            } else {
                throw new \InvalidArgumentException(sprintf("Task with ID: %d was already completed !!!", $id));
            }
        } else {
            throw new \InvalidArgumentException(sprintf("Task with ID: %d not found !!!", $id));
        }
    }
}