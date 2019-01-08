<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\InputValidator;
use AppBundle\Entity\Message;

class DefaultController extends Controller
{
    protected $response = array('status' => '', 'data' => '', 'message' => '');
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        try{
            $inputValidator = new InputValidator();

            $Input = $inputValidator->validateInputMethod($request);
            if($Input) {
                $this->processMessage($Input);
            }
            return $this->sendResponse($this->response);
        }
        catch(\Exception  $e){
            $this->response['status'] = 'error';
            $this->response['message'] = $e->getMessage();
            return $this->sendResponse($this->response);
        }
    }



    private function processMessage($data){
        print_r($data);
        $em = $this->getDoctrine()->getManager();

        $newMsg = new Message();
        $newMsg->setCreated(new \DateTime('now'));
        $newMsg->setOriginator($data['originator']);
        $newMsg->setRecipient($data['recipient']);
        $newMsg->setMessage($data['message']);
        $newMsg->setIsSent(false);
        $em->persist($newMsg);
        $em->flush();
        $msg = $em->getRepository('AppBundle:Message')->findOneBy(array('isSent' => true), array('sentDate' => 'DESC'));
        print_r($msg);
        die();
        $this->response['status'] = 'success';
        $this->response['message'] = 'message have been sent';
    }
    private function sendResponse($response){

        return new JsonResponse($response);
    }
}
