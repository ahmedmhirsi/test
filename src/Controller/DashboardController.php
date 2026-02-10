<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        if ($this->isGranted('ROLE_EMPLOYEE')) {
            return $this->redirectToRoute('employee_dashboard');
        }
        
        if ($this->isGranted('ROLE_CLIENT')) {
            return $this->redirectToRoute('app_client_dashboard');
        }
        
        if ($this->isGranted('ROLE_VISITEUR')) {
            return $this->redirectToRoute('visiteur_dashboard');
        }

        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(UserRepository $userRepository): Response
    {
        $allUsers = $userRepository->findAll();
        $totalUsers = count($allUsers);
        
        $activeUsers = count(array_filter($allUsers, fn($u) => $u->isActive()));
        
        $clients = count(array_filter($allUsers, fn($u) => in_array('ROLE_CLIENT', $u->getRoles())));
        $employees = count(array_filter($allUsers, fn($u) => in_array('ROLE_EMPLOYEE', $u->getRoles())));
        $admins = count(array_filter($allUsers, fn($u) => in_array('ROLE_ADMIN', $u->getRoles())));
        
        // Get recent users (last 5)
        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);
        
        return $this->render('back_office/dashboard.html.twig', [
            'user' => $this->getUser(),
            'stats' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'clients' => $clients,
                'employees' => $employees,
                'admins' => $admins,
            ],
            'recent_users' => $recentUsers,
            'client_stats' => [], // Empty for now, will be filled when we have candidature entity
        ]);
    }

    #[Route('/employee/dashboard', name: 'employee_dashboard')]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function employeeDashboard(): Response
    {
        return $this->render('dashboard/employee.html.twig', [
            'user' => $this->getUser()
        ]);
    }


    #[Route('/visiteur/dashboard', name: 'visiteur_dashboard')]
    #[IsGranted('ROLE_VISITEUR')]
    public function visiteurDashboard(): Response
    {
        return $this->render('dashboard/visiteur.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
