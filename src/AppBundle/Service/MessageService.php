<?php
/**
 * Created by PhpStorm.
 * User: iuli
 * Date: 1/8/2019
 * Time: 8:33 PM
 */

namespace AppBundle\Service;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\Message;

class MessageService
{
    /** @var RegistryInterface */
    private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function process($data){
        $data['date'] = new \DateTime('now');
        $msgId = $this->saveMessage($data);
        $msgoId = $this->getLastMessageSendDate();
        //print_r($data);

        //die();
        //$this->response['status'] = 'success';
        //$this->response['message'] = 'message have been sent';
        return array('status' => 'success', 'data' => '', 'message' => 'message have been sent');
    }

    private function saveMessage($data){
        $newMsg = new Message();
        $newMsg->setCreated($data['date']);
        $newMsg->setOriginator($data['originator']);
        $newMsg->setRecipient($data['recipient']);
        $newMsg->setMessage($data['message']);
        $newMsg->setIsSent(false);
        $em = $this->doctrine->getManager();
        $em->persist($newMsg);
        $em->flush();
        return $newMsg->getId();
    }

    private function getLastMessageSendDate(){
        $em = $this->doctrine->getManager();
        $msg = $em->getRepository('AppBundle:Message')->findOneBy(array('isSent' => true), array('sentDate' => 'DESC'));
        return $msg->getSentDate();
    }
}