<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/article")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("", name="article_index")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('Article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/create", name="article_create")
     */
    public function createArticle(Request $request, AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $author = $authorRepository->find(1);
            $article = $form->getData();
            $articleToSubmit = new Article();
            $articleToSubmit->setTitle($article->getTitle());
            $articleToSubmit->setContent($article->getContent());
            $articleToSubmit->setAuthor(
                $author
            );
            $articleToSubmit->setCreatedAt(new \DateTimeImmutable());
            $articleToSubmit->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($articleToSubmit);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('Article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/fetch", name="article_show")
     */
    public function getArticle(Article $article): Response
    {
        return $this->render('Article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/update", name="article_update")
     * @Security("is_granted('ROLE_USER') and article.getAuthor() == user")
     */
    public function updateArticle(Request $request, Article $article, AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            $author = $authorRepository->find(1);
            $articleData = $form->getData();
            $article->setTitle($articleData->getTitle());
            $article->setContent($articleData->getContent());
            $article->setAuthor(
                $author
            );
            $article->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('Article/create.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="article_delete")
     * @Security("is_granted('ROLE_USER') and article.getAuthor() == user")
     */
    public function deleteArticle(Article $article, EntityManagerInterface $entityManager, Session $session): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('article_index');
    }
}
