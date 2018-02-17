<?php

namespace AppBundle\Controller;



use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Music;



class MainController extends Controller
{
    /**
     * @Route("/{id}", name="play_site")
     */
    public function playAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getRepository(Music::class);
        $music = $em->findOneBy(["id" => $id]);
        $id_list = $em->findAll();
        $list = array();
        foreach($id_list as $item){
            array_push($list,$item->getId());
        }


        $current_position = array_search($id, $list);
        $next_song = ($id_list[$current_position] == end($id_list)? end($id_list) : $id_list[$current_position+1]);
        $prev_song = (($current_position-1)<0? false : $id_list[$current_position-1]);

        $this->updateViews($id);

        return $this->render('index.html.twig',[
            "list" => $music, "playlist" => ["current" => $id, "next" => $next_song, "prev" => $prev_song]
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/", name="homepage")
     */
    public function indexAction(){
        return $this->redirectToRoute("play_site", ["id"=>1]);
    }

    private function updateViews($id){
        $em = $this->getDoctrine()->getRepository(Music::class);
        $one = $em->findOneBy(["id" => $id]);
        $entityManager = $this->getDoctrine()->getManager();

        $one->setViews($one->getViews()+1);
        $entityManager->flush();

    }
    /**
     * @Route("/list/all", name="music_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allAction()
    {
        $em = $this->getDoctrine()->getRepository(Music::class);
        $id_list = $em->findAll();
        return $this->render('new.html.twig',["list" => $id_list]);
    }

    /**
     * @Route("/list/top", name="music_list_top")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topAction()
    {
        $em = $this->getDoctrine()->getRepository(Music::class);
        $id_list = $em->findBy([],['voteplus' => 'DESC']);
        return $this->render('top.html.twig',["list" => array_slice($id_list,0,10)]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/list/add", name="add_new")
     */
    public function addAction(Request $request){

        $Music = new Music();

        $error = false;

        $form = $this->createFormBuilder($Music)
            ->add('url', TextType::class,["label" =>"Paste URL to YouTube song", "attr" => ["class" => "form-control"]])
            ->add('submit', SubmitType::class, ["label" => "Add", "attr" => ["class" => "btn btn-primary mt-2"]])
            ->getForm();

        $form->handleRequest($request);
        $content = null;

        $yt = new YoutubeController();

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $yt->checkYoutube($Music->getUrl());

            if($url && !$this->isExist($url)){

                $title = $yt->dataFromApiAction($url);
                if($title) {
                    $list = $this->getList();
                    $endOf = end($list);
                    $content = ["title" => $title, "url" => $url, "id" => $endOf->getId()];

                    $Music->setName($title);
                    $Music->setUrl($url);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($Music);
                    $em->flush();

                } else{
                    $error = true;
                }
            } else{
                $error = true;
            }
        }


        return $this->render('add.html.twig',["form" => $form->createView(), "error" => $error, "content" => $content]);
    }

    /**
     * @param $url
     * @return Music|object
     */
    private function isExist($url){
        $em = $this->getDoctrine()->getRepository(Music::class);
        $resp = $em->findOneBy(["url" => $url]);
        return $resp;
    }

    /**
     * @return Music[]|array
     */
    private function getList(){
        $em = $this->getDoctrine()->getRepository(Music::class);
        $id_list = $em->findAll();

        return $id_list;
    }




}