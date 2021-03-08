<?php

namespace App\Controller\Api;

use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TaskController
 *
 * @package App\Controller
 * @Route("/api", name="tasks_api")
 */
class TaskController extends AbstractController
{
    /**
     * @param TaskRepository $taskRepository
     * @return JsonResponse
     * @Route("/tasks", name="tasks", methods={"GET"})
     */
    public function getTasks(TaskRepository $taskRepository): JsonResponse
    {
        $data = $taskRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param TaskService $taskService
     * @return JsonResponse
     *
     * @Route("/tasks", name="tasks_create", methods={"POST"})
     */
    public function createTask(Request $request, TaskService $taskService): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $data = $request->request->all();

            $file = $request->files->get('file') ?? null;

            $errors = $taskService->createTask($data, $file);

            if (empty($errors)) {
                $responseCode = Response::HTTP_CREATED;
                $responseData = [
                    'status' => $responseCode,
                    'result' => "Task added successfully",
                ];
                return $this->response($responseData);
            } else {
                $responseCode = Response::HTTP_BAD_REQUEST;
                $responseData = [
                    'status' => $responseCode,
                    'errors' => $errors,
                ];
            }
            return $this->response($responseData, $responseCode);
        } catch (\Exception $exception) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = [
                'status' => $responseCode,
                'errors' => [$exception->getMessage()],
            ];
        }
        return $this->response($responseData, $responseCode);
    }

    /**
     * @param TaskRepository $taskRepository
     * @param int $id
     * @return JsonResponse
     * @Route("/tasks/{id}", name="tasks_get", methods={"GET"})
     */
    public function getTask(TaskRepository $taskRepository, int $id): JsonResponse
    {
        $task = $taskRepository->find($id);
        if (!$task){
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Task not found",
            ];
            return $this->response($data, Response::HTTP_NOT_FOUND);
        }
        return $this->response($task);
    }

    /**
     * @param Request $request
     * @param TaskService $taskService
     * @param int $id
     * @return JsonResponse
     * @Route("/tasks/{id}", name="tasks_put", methods={"PATCH"})
     */
    public function updateTask(Request $request, TaskService $taskService, int $id): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $data = $request->request->all();
            $errors = $taskService->updateTask($data, $id);

            if (empty($errors)) {
                $responseCode = Response::HTTP_OK;
                $responseData = [
                    'status' => $responseCode,
                    'result' => "Task updated successfully",
                ];
                return $this->response($responseData);
            } else {
                $responseCode = Response::HTTP_BAD_REQUEST;
                $responseData = [
                    'status' => $responseCode,
                    'errors' => $errors,
                ];
            }
            return $this->response($responseData, $responseCode);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $responseCode = Response::HTTP_BAD_REQUEST;
            $responseData = [
                'status' => $responseCode,
                'errors' => [$invalidArgumentException->getMessage()],
            ];
        } catch (\Exception $exception) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = [
                'status' => $responseCode,
                'errors' => [$exception->getMessage()],
            ];
        }
        return $this->response($responseData, $responseCode);
    }

    /**
     * @param Request $request
     * @param TaskService $taskService
     * @param int $id
     * @return JsonResponse
     * @Route("/tasks/{id}/validate", name="tasks_put", methods={"POST"})
     */
    public function validateTask(Request $request, TaskService $taskService, int $id): JsonResponse
    {
        try {
            $taskService->validateTask($id);

            $responseCode = Response::HTTP_OK;
            $responseData = [
                'status' => $responseCode,
                'result' => "Task validated successfully",
            ];
            return $this->response($responseData);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $responseCode = Response::HTTP_BAD_REQUEST;
            $responseData = [
                'status' => $responseCode,
                'errors' => [$invalidArgumentException->getMessage()],
            ];
        } catch (\Exception $exception) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = [
                'status' => $responseCode,
                'errors' => [$exception->getMessage()],
            ];
        }
        return $this->response($responseData, $responseCode);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param TaskRepository $taskRepository
     * @param int $id
     * @return JsonResponse
     * @Route("/tasks/{id}", name="tasks_delete", methods={"DELETE"})
     */
    public function deleteTask(EntityManagerInterface $entityManager, TaskRepository $taskRepository, int $id): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task){
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Task not found",
            ];
            return $this->response($data, Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($task);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'result' => "Task deleted successfully",
        ];
        return $this->response($data);
    }

    /**
     *  Returns a JSON response
     *
     * @param $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param Request $request
     * @return Request
     */
    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}