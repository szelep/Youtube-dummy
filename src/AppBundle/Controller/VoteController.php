<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Music;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class VoteController extends MainController
{
    /**
     * @param $set
     * @param $id
     * @return JsonResponse
     * @Route("/vote/{set}/{id}", name="vote", methods={"GET","POST"})
     */
    public function voteAction($set,$id){

        $resp = $this->fetchData($id);

        if($set == 'up' || $set == 'down'){

            if($resp && !$this->checkVotes($id)) {
                $this->pushVote($set,$id);
                $this->updateSession($id);

                $response = $this->genJson(1,false);
            } else{
                $response = $this->genJson(4,false);
            }


        } else if($set == 'get') {
            $votes = ['votePlus' => $resp->getVoteplus(), 'voteMinus' =>$resp->getVoteminus()];
            $response = $this->genJson(2,$votes);
        } else{
            $response = $this->genJson(3, false);
        }

        return($response);
    }

    /**
     * @param $e
     * @param $content
     * @return JsonResponse
     */
    private function genJson($e,$content){

        switch($e){
            case 1 : $state = 'success'; $message = 'Ok'; break;
            case 2 : $state = 'success'; $message = $content; break;
            case 3 : $state = 'error'; $message = 'Not allowed method!'; break;
            case 4 : $state = 'error'; $message = 'Already voted!'; break;
        }

        return new JsonResponse([$state,$message]);
    }

    /**
     * @param $set
     * @param $id
     * @return bool
     */
    private function pushVote($set,$id){
        $em = $this->getDoctrine()->getRepository(Music::class);
        $one = $em->findOneBy(["id" => $id]);
        $entityManager = $this->getDoctrine()->getManager();

        if($set == 'up'){
            $one->setVoteplus($one->getVoteplus()+1);
        }  else{
            $one->setVoteminus($one->getVoteminus()+1);
        }
        $entityManager->flush();

        return true;
    }

    /**
     * @param $id
     * @return Dupsko|object
     */
    private function fetchData($id){
        $em = $this->getDoctrine()->getRepository(Music::class);
        $one = $em->findOneBy(["id" => $id]);
        return $one;
    }

    /**
     * @param $id
     * @return bool
     */
    private function checkVotes($id)
    {
        $session = new Session();
        if(!$session->get('votes')){
            $session->set('votes',[]);
        }
        $arr = $session->get('votes');
        if (in_array($id, $arr)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     */
    private function updateSession($id){
        $session = new Session();
        $votes = $session->get('votes');
        array_push($votes,$id);
        $session->set('votes',$votes);
    }
}