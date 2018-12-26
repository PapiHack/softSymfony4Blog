<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Form\PostType;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repo->findAll();

        return $this->render('blog/index.html.twig', ['posts' => $posts]);
    }

    /**
     * @Route("/blog/add", name="add", methods={"GET", "POST"})
     */
    public function add(Request $request)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $this->generateSlug($post);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();

                $request->getSession()->getFlashbag()->add('ajout', 'Post bien ajouté !');
                return $this->redirectToRoute('blog');
            }
        }

        return $this->render('blog/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/blog/show/{slug}", name="show", methods={"GET"})
     */
    public function show(Request $request, $slug)
    {
        $repo = $this->getDoctrine()->getRepository(Post::class);
        $post = $repo->findOneBySlug($slug);

        return $this->render('blog/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/blog/edit/{slug}", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $slug)
    {
        $repo = $this->getDoctrine()->getRepository(Post::class);
        $post = $repo->findOneBySlug($slug);

        $form = $this->createForm(PostType::class, $post);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            
            if($form->isValid())
            {
                $this->generateSlug($post);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();

                $request->getSession()->getFlashbag()->add('modif', 'Post modifié !');
                return $this->redirectToRoute('blog');
            }
        }

        return $this->render('blog/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/delete/{slug}", name="delete")
     */
    public function delete(Request $request, $slug)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBySlug($slug);
        $entityManager->remove($post);
        $entityManager->flush();

        $request->getSession()->getFlashbag()->add('delete', 'Post supprimé !');
        return $this->redirectToRoute('blog');
    }

    public function generateSlug(Post $post)
    {
        $slug = strtolower(str_replace(' ', '-', $post->getTitre()));
        $post->setSlug($slug);
    }
}
