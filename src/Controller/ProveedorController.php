<?php

namespace App\Controller;

use App\Entity\Proveedor;
use App\Form\ProveedorType;
use App\Repository\ProveedorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proveedor')]
final class ProveedorController extends AbstractController
{
    #[Route(name: 'app_proveedor_index', methods: ['GET'])]
    public function index(Request $request, ProveedorRepository $proveedorRepository, PaginatorInterface $paginator): Response
    {
        $query = $proveedorRepository->createQueryBuilder('p')
            ->orderBy('p.nombre', 'ASC')
            ->getQuery();

        $proveedors = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Estadísticas totales
        $totalProveedores = $proveedorRepository->count([]);
        $totalConTelefono = $proveedorRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConEmail = $proveedorRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConDireccion = $proveedorRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('proveedor/index.html.twig', [
            'proveedors' => $proveedors,
            'totalProveedores' => $totalProveedores,
            'totalConTelefono' => $totalConTelefono,
            'totalConEmail' => $totalConEmail,
            'totalConDireccion' => $totalConDireccion,
        ]);
    }

    #[Route('/new', name: 'app_proveedor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $proveedor = new Proveedor();
        $form = $this->createForm(ProveedorType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($proveedor);
            $entityManager->flush();

            $this->addFlash('success', 'El proveedor ha sido creado correctamente.');
            return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('proveedor/new.html.twig', [
            'proveedor' => $proveedor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_proveedor_show', methods: ['GET'])]
    public function show(Proveedor $proveedor): Response
    {
        return $this->render('proveedor/show.html.twig', [
            'proveedor' => $proveedor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_proveedor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proveedor $proveedor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProveedorType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'El proveedor ha sido actualizado correctamente.');
            return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('proveedor/edit.html.twig', [
            'proveedor' => $proveedor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_proveedor_delete', methods: ['POST'])]
    public function delete(Request $request, Proveedor $proveedor, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $proveedor->getId()->toRfc4122(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($proveedor);
                $entityManager->flush();
                $this->addFlash('success', 'El proveedor ha sido eliminado correctamente.');
            } else {
                $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el proveedor.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
    }
}
