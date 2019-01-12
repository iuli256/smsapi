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
        date_default_timezone_set("Europe/Bucharest");
    }

    public function cron($smskey){
        $data['date'] = new \DateTime('now');
        $lastSendDate = $this->getLastMessageSendDate();
        $lastUnsentMessage = $this->getLastUnsentMessage();
        $dif = $data['date']->getTimestamp() - $lastSendDate->getTimestamp();
        if ($dif >= 1){
            return $this->sendMessage($lastUnsentMessage, $smskey);
        }else {
            return array('status' => 'error', 'data' => '', 'message' => 'message put in que list because it can be sent just one per second');
        }
    }
    public function process($data, $smskey){
        $data['date'] = new \DateTime('now');
        $data['id'] = $this->saveMessage($data);
        $lastSendDate = $this->getLastMessageSendDate();
        $dif = $data['date']->getTimestamp() - $lastSendDate->getTimestamp();
        if ($dif >= 1){
            return $this->sendMessage($data, $smskey);
        }else {
            return array('status' => 'error', 'data' => '', 'message' => 'message put in que list because it can be sent just one per second');
        }
    }

    public function sendMessage($data, $smskey){
        $MessageBird = new \MessageBird\Client($smskey); // Set your own API access key here.
        $Message             = new \MessageBird\Objects\Message();
        $Message->originator = 'MessageBird';
        $Message->recipients = array($data['recipient']);
        $Message->body       = $data['message'];
        $sendResponse = "";
        $status = "";
        $MessageResult = "";
        try {
            $MessageResult = $MessageBird->messages->create($Message);
            $sendResponse =  'message sent';
            $status = "success";
        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            // That means that your accessKey is unknown
            $sendResponse =  'wrong login';
            $status = "error";
        } catch (\MessageBird\Exceptions\BalanceException $e) {
            // That means that you are out of credits, so do something about it.
            $sendResponse =   'no balance';
            $status = "error";
        } catch (\Exception $e) {
            $sendResponse =   $e->getMessage();
            $status = "error";
        }
        $this->updateMessage(array('id' => $data['id'], 'response' => $sendResponse));
        return array('status' => $status, 'data' => $MessageResult, 'message' => $sendResponse);
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

    private function getLastUnsentMessage(){
        $em = $this->doctrine->getManager();
        $msg = $em->getRepository('AppBundle:Message')->findOneBy(array('isSent' => false), array('created' => 'ASC'));
        $response = array('id' => $msg->getId(),
                            'originator' => $msg->getOriginator(),
                            'recipient' => $msg->getRecipient(),
                            'message' => $msg->getMessage());
        return $response;
    }
}