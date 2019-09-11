<?php

namespace App\Controller;

use App\Entity\Tip;
use App\Form\Type\TipType;
use App\Service\FileUploader;
use App\Repository\TipRepository;
use App\Voter\TipVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TipController extends AbstractController
{
    private $tipRepository;
    private $entityManager;

    public function __construct(TipRepository $tipRepository, EntityManagerInterface $entityManager)
    {
        $this->entityManager  = $entityManager;
        $this->tipRepository = $tipRepository;
    }

    /**
     * @Route("/", name="tip:index")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $tips = $paginator->paginate($this->tipRepository->findAll(), $request->query->getInt('page', 1), 25);
        // $tips = $this->tipRepository->findAll();
        return $this->render('tip/index.html.twig', compact("tips"));
    }

    /**
     * @Route("/tip/create", name="tip:create")
     */
    public function createTip(Request $request)
    {
        $tip = new Tip();
        $form = $this->createForm(TipType::class, $tip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tip = $form->getData();
            $tip->setUser($this->getUser());

            $this->entityManager->persist($tip);
            $this->entityManager->flush();

            $this->addFlash('success', "Your tip has been added =D");

            return $this->redirectToRoute("tip:index");
        }

        return $this->render('tip/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tip/edit/{tipId}", name="tip:edit")
     */
    public function editTip(Request $request, UploaderHelper $fileUploader, CacheManager $cache,  $tipId)
    {
        // dd($this->getParameter('upload_directory'));
        $tip = $this->tipRepository->find($tipId);
        try {
            $this->denyAccessUnlessGranted("edit", $tip);
        } catch (\Exception $th) {
            $this->addFlash('error', "You can't edit a tip you haven't created.");
            return $this->redirectToRoute("tip:index");
        }
        $form = $this->createForm(TipType::class, $tip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tip = $form->getData();
            $tip->setUser($this->getUser());
            $this->entityManager->persist($tip);
            $this->entityManager->flush();

            $this->addFlash('success', "Your tip has been updated.");

            return $this->redirectToRoute("tip:index");
        }

        return $this->render('tip/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tip/delete/{tipId}", name="tip:delete")
     */
    public function deleteTip($tipId)
    {
        $tip = $this->tipRepository->find($tipId);

        if ($tip) {
            $this->entityManager->remove($tip);
            $this->entityManager->flush();

            $this->addFlash('success', "Your tip has been deleted.");

            return $this->redirectToRoute("tip:index");
        }
        $this->addFlash('error', "An error occured while trying to delete your tip.");

        return $this->redirectToRoute("tip:index");
    }
}
