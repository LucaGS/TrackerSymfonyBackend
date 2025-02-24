<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/user', name: 'api_user_')]
class ApiUserController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->json($users);
    }

    #[Route('/Register', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Email and password are required'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Email already exists'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User created!'], 201);
    }
    #[Route('/Login', methods: ['POST'])]
    public function loginUser(Request $request, EntityManagerInterface $entityManager,):JsonResponse{
        $data = json_decode($request->getContent(),true);
        if(!isset($data['email'])|| !isset($data['password'])){
            return $this->json(['error' => 'Missing Email or password'],400);
        }
        $user = $entityManager->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if(!$user){
            return $this->json(['error'=>'Could not find an user with this mail:' .$data['email']],400);
            if(!password_verify($data['password'],$user->getPassword())){
                return $this->json(['error' => 'Invalid password'], 401);
            }
        } return $this->json(['userId' => $user->getId()]);
    }
}
