<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ArticleType;
use App\Entity\PropertySearch;
use App\Form\ArticleSearchType;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;
class IndexController extends AbstractController

{
    #[Route('/', name: 'article_list')]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(ArticleSearchType::class, $propertySearch);
        $form->handleRequest($request);

        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();
            $repository = $entityManager->getRepository(Article::class);

            if ($nom !== "") {
                $articles = $repository->findBy(['nom' => $nom]);
            } else {
                $articles = $repository->findAll();
            }
        }
        return $this->render('articles/index.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    #[Route('/article/save', name: 'article_save')]
    public function save(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setNom('Article 3');
        $article->setPrix(3000);
        $entityManager->persist($article);
        $entityManager->flush();
        return new Response('Article enregisté avec id ' . $article->getId());
    }

    #[Route('/articles/new',name:'new_article',methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
    }
    return $this->render('articles/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/article/{id}', name: 'article_show')]
    public function show($id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found with id ' . $id);
        }
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé pour cet ID: ' . $id);
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
        }
        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

#[Route('/article/delete/{id}', name: 'delete_article', methods: ['DELETE','GET'])]
public function delete(Request $request, $id, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->find($id);
    if (!$article) {
        throw $this->createNotFoundException('Aucun article trouvé pour cet ID: ' . $id);
    }
    $entityManager->remove($article);
    $entityManager->flush();

    $response = new Response();
    $response->send();
    return $this->redirectToRoute('article_list');
}

    #[Route('/category/newCat', name: 'new_category', methods: ['POST','GET'])]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $entityManager->persist($category);
            $entityManager->flush();
        }
        return $this->render('categories/NewCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/art_cat/', name: 'article_par_cat', methods: ['GET', 'POST'])]
    public function articlesParCategorie(Request $request)
    {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);
        
        $articles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
            if ($category) { // Check if category is not null
                $articles = $category->getArticles();
            } else {
                $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
            }
        }

        return $this->render('articles/articlesParCategorie.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    #[Route('/art_prix/', name: 'article_par_prix', methods: ['GET', 'POST'])]
    public function articlesParPrix(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);
        
        $articles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            
            $repository = $entityManager->getRepository(Article::class);
            $articles = $repository->findByPriceRange($minPrice, $maxPrice);
        }

        return $this->render('articles/articlesParPrix.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

}
