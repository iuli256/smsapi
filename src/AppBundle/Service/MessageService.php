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
use Monolog\Logger;

class MessageService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var SrnLoggerService
     */
    protected $logger;

    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
        return $this;
    }

    /**
     * @param SrnLoggerService $logger
     * @return AvailableStockService
     */
    public function setLogger(SrnLoggerService $logger): AvailableStockService
    {
        $this->logger = $logger;
        return $this;
    }
    public function __construct(RegistryInterface $doctrine)
    {
       // $this->doctrine = $doctrine;
        date_default_timezone_set("Europe/Bucharest");
    }

    public function cron(){
        $data['date'] = new \DateTime('now');
        $lastSendDate = $this->getLastMessageSendDate();
        $lastUnsentMessage = $this->getLastUnsentMessage();
        $dif = $data['date']->getTimestamp() - $lastSendDate->getTimestamp();
        if ($dif >= 1){
            $this->sendMessage($data);
        }
    }
    public function process($data){
        $data['date'] = new \DateTime('now');
        $data['id'] = $this->saveMessage($data);
        $lastSendDate = $this->getLastMessageSendDate();
        $dif = $data['date']->getTimestamp() - $lastSendDate->getTimestamp();
        if ($dif >= 1){
            $this->sendMessage($data);
        }
        //$this->response['status'] = 'success';
        //$this->response['message'] = 'message have been sent';
        return array('status' => 'success', 'data' => '', 'message' => 'message have been sent');
    }

    public function sendMessage($data){
        $MessageBird = new \MessageBird\Client('klH4as6AMjdz8hhnOsyyKsGjW'); // Set your own API access key here.
        $Message             = new \MessageBird\Objects\Message();
        $Message->originator = 'MessageBird';
        $Message->recipients = array($data['recipient']);
        $Message->body       = $data['message'];
        $sendResponse = "";
        try {
            $MessageResult = $MessageBird->messages->create($Message);
            print_r($MessageResult);
            $sendResponse =  'message sent';
        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            // That means that your accessKey is unknown
            $sendResponse =  'wrong login';
        } catch (\MessageBird\Exceptions\BalanceException $e) {
            // That means that you are out of credits, so do something about it.
            $sendResponse =   'no balance';
        } catch (\Exception $e) {
            $sendResponse =   $e->getMessage();
        }
        $this->updateMessage(array('id' => $data['id'], 'response' => $sendResponse));
    }

    private function updateMessage($data){
        $em = $this->doctrine->getManager();
        $msg = $em->getRepository('AppBundle:Message')->find($data['id']);
        $msg->setResponse($data['response']);
        $msg->setIsSent(true);
        $msg->setSentDate(new \DateTime('now'));
        $em->flush();
        return $msg->getSentDate();
    }

    private function saveMessage($data){
        try{
            $newMsg = new Message();
            $newMsg->setCreated($data['date']);
            $newMsg->setOriginator($data['originator']);
            $newMsg->setRecipient($data['recipient']);
            $newMsg->setMessage($data['message']);
            $newMsg->setIsSent(false);
            $em = $this->doctrine->getManager();
            $em->persist($newMsg);

        }catch (\Exception $e){

        }
        $em->flush();
        return $newMsg->getId();
    }

    private function getLastMessageSendDate(){
        $em = $this->doctrine->getManager();
        $msg = $em->getRepository('AppBundle:Message')->findOneBy(array('isSent' => true), array('sentDate' => 'DESC'));
        return $msg->getSentDate();
    }

    private function getLastUnsentMessage(){
        $em = $this->doctrine->getManager();
        $msg = $em->getRepository('AppBundle:Message')->findOneBy(array('isSent' => false), array('created' => 'ASC'));
        return $msg->getId();
    }
}