<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Comment;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $query = $this->getDoctrine()->getRepository(Post::class)->findBy(array(),array('id' => 'DESC'));

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4/*limit per page*/
        );

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();

        return $this->render('home/index.html.twig', [
            'posts' => $pagination,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/new", name="home_post_new")
     */
    public function newPost(Request $request, SluggerInterface $slugger)
    {

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('path')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);

                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setPath($newFilename);
            }

            $post->setSlug($this->slug($form->get("title")->getData()));
            $post->setUser($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());
            $post->setPath($newFilename);

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('home_page');
        }

        return $this->render('home/newPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }


/**
 * @Route("/post/{slug}", name="home_post_show")
 */
public function showPost($slug)
{
    $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['slug' => $slug,]);
    $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
    $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();
      
    return $this->render('home/showPost.html.twig', [
        'post' => $post,
        'categories' => $categories,
        'tags' => $tags,
    ]);

}

    /**
     * @Route("/post/comment/new", name="post_comment_newComment")
     */
    public function newComment(Request $request)
    {

        $post = $this->getDoctrine()->getRepository(Post::class)->find($request->get("post"));

        $entityManager = $this->getDoctrine()->getManager();
        
        $comment = new Comment();
        $comment->setUser($this->getUser());
        $comment->setContent($request->get("content"));
        $comment->setPost($post);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('home_post_show', array(
            'slug' => $post->getSlug()
            )); 

    }

/**
 * @Route("/post/category/{category}", name="home_post_category")
 */
public function categoryPost(PaginatorInterface $paginator, Request $request, $category)
{

    /* $query = $this->getDoctrine()->getRepository(Post::class)->findBy(['category' => $category], array('id' => 'DESC')); */

    $query = $this->getDoctrine()->getRepository(Post::class)->dameCategory($category);

    $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        4/*limit per page*/
    );

    $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
    $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();

    return $this->render('home/index.html.twig', [
        'posts' => $pagination,
        'categories' => $categories,
        'tags' => $tags,
    ]);
    
}


/**
 * @Route("/tag/{tags}", name="front_tag")
 */
public function tagAction(PaginatorInterface $paginator, Request $request, $tags)
{

        $query = $this->getDoctrine()->getRepository(Post::class)->dameTags($tags);

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();
        
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4/*limit per page*/
        );
    
        return $this->render('home/index.html.twig', [
            'posts' => $pagination,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    
}


/**
 * @Route("/search/item", name="front_search")
 */
public function searchAction(PaginatorInterface $paginator, Request $request)
{

        $query = $this->getDoctrine()->getRepository(Post::class)->postSearch($request->request->get("item"));

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            12/*limit per page*/
        );

    return $this->render('home/search.html.twig', [
        'posts' => $pagination,
        'categories' => $categories,
        'tags' => $tags,
    ]);

}


    private function slug($string)
    {
        $characters = array(
            "Á" => "A", "Ç" => "c", "É" => "e", "Í" => "i", "Ñ" => "n", "Ó" => "o", "Ú" => "u",
            "á" => "a", "ç" => "c", "é" => "e", "í" => "i", "ñ" => "n", "ó" => "o", "ú" => "u",
            "à" => "a", "è" => "e", "ì" => "i", "ò" => "o", "ù" => "u",
        );

        $string = strtr($string, $characters);
        $string = strtolower(trim($string));
        $string = preg_replace("/[^a-z0-9-]/", "-", $string);
        $string = preg_replace("/-+/", "-", $string);

        if (substr($string, strlen($string) - 1, strlen($string)) === "-") {
            $string = substr($string, 0, strlen($string) - 1);
        }

        return $string;
    }

    
}
