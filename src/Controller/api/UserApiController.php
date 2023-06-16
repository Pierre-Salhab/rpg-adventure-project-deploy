<?php

namespace App\Controller\api;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserApiController extends CoreApiController
{
    // ! Pas de Browse
    
    // ! Read alias currentUser
    /**
     * Route giving CurrentUser details from JWT Token 
     * 
     * @Route("/api/users/details", name="app_api_users_details", methods={"GET"})
     */
    public function currentUser(): JsonResponse
    {
        /** @var App\Entity\User $user */
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est authentifié
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié.'], 401);
        }

        // Récupérez les détails de l'utilisateur connecté
        $userId = $user->getId();
        $email = $user->getEmail();
        $pseudo = $user->getPseudo();
        $avatar = $user->getAvatar();

        // Retournez les détails de l'utilisateur au format JSON
        return new JsonResponse([
            'id' => $userId,
            'email' => $email,
            'pseudo' => $pseudo,
            'avatar' => $avatar,
        ]);
    }

    // ! Add one user
    /**
     * New user
     *
     * @Route("/api/users",name="app_api_users_add", methods={"POST"})
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     */
    public function add(
        Request $request,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validatorInterface,
        UserPasswordHasherInterface $passwordHasher
    ) {
        // Récupérer le contenu JSON
        $jsonContent = $request->getContent();
        // Désérialiser (convertir) le JSON en entité Doctrine User
        try { // on tente de désérialiser
            $user = $serializer->deserialize($jsonContent, User::class, 'json');
        } catch (Exception $exception) {
            // Si on n'y arrive pas, on passe ici
            // dd($exception);
            // code 400 ou 422
            return $this->json("JSON Invalide", Response::HTTP_BAD_REQUEST);
        }

        // on valide les données de notre entité
        $errors = $validatorInterface->validate($user);
        // Y'a-t-il des erreurs ?
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $plainPassword = $user->getPassword();
        if (!empty($plainPassword)) {

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);
        }
        // On sauvegarde l'entité
        $userRepository->add($user, true);

        return $this->json201($user, ["user_create"]);
    }

    // ! Edit one user

    /**
     * edit user
     *
     * @Route("/api/users/{id}",name="app_api_users_edit", requirements={"id"="\d+"}, methods={"PUT", "PATCH"})
     * 
     * @param Request $request la requete
     * @param SerializerInterface $serializerInterface
     * @param UserRepository $userRepository
     */
    public function edit(
        $id,
        Request $request,
        SerializerInterface $serializerInterface,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $jsonContent = $request->getContent();
        $user = $userRepository->find($id);
        $serializerInterface->deserialize(
            $jsonContent,
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );
        $plainPassword = $user->getPassword();
        if (!empty($plainPassword)) {

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);
        }

        $userRepository->add($user, true);

        return $this->json200($user, ["user_read"]);
    }

    /**
     * delete user
     *
     * @Route("/api/users/{id}",name="app_api_users_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete($id, userRepository $userRepository)
    {
        $user = $userRepository->find($id);
        $userRepository->remove($user, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
