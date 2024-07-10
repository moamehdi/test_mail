<?php

namespace App\Controller;
use Knp\Snappy\Pdf;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;

class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_mail')]
    public function index(UserRepository $userRepository,Pdf $knpSnappyPdf ): Response
    {
        $user = $this->getUser();


        return $this->render('mail/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/mail/generate-pdf', name: 'generate_pdf', methods: ['GET'])]
    public function generatePdf(Pdf $knpSnappyPdf,MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $filename = $user->getId() . '.pdf';
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
        $knpSnappyPdf->generateFromHtml(
            $this->renderView(
                'mail/email_pdf.html.twig',
                array(
                    'user'  => $user
                )
            ),
            $filePath
        );

        $email = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($user->getEmail())
            ->subject('Votre PDF')
            ->text('Veuillez trouver ci-joint votre PDF.')
            ->attachFromPath($filePath)
        ->addPart(new DataPart(new File($user->getId())));
        $mailer->send($email);

        return $this->redirectToRoute('app_mail', [
            'pdf_path' => $filename,
        ]);
    }
    #[Route('/mail/send-mail', name: 'send_mail', methods: ['GET'])]
    public function sendMail(MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        $email = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($user->getEmail())
            ->subject('Votre PDF')
            ->text('Veuillez trouver ci-joint votre PDF.')
            ->addPart(new DataPart(new File($user->getId().".pdf")));
        $mailer->send($email);

        return $this->redirectToRoute('app_mail', [
        ]);
    }
}

