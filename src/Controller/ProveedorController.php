<?php

namespace App\Controller;

use App\Entity\Proveedor;
use App\Form\ProveedorType;
use App\Service\Proveedor\Interface\ProveedorOperationsInterface;
use App\Service\Proveedor\Interface\ProveedorSearchInterface;
use App\Service\Proveedor\Interface\ProveedorStatsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proveedor')]
final class ProveedorController extends AbstractController
{
    public function __construct(
        private ProveedorSearchInterface $searchService,
        private ProveedorStatsInterface $statsService,
        private ProveedorOperationsInterface $operationsService
    ) {}

    #[Route(name: 'app_proveedor_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

        return $this->render('proveedor/index.html.twig', [
            'proveedors' => $searchResult['pagination'],
            'totalProveedores' => $statistics['totalProveedores'],
            'totalConTelefono' => $statistics['totalConTelefono'],
            'totalConEmail' => $statistics['totalConEmail'],
            'totalConDireccion' => $statistics['totalConDireccion'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_proveedor_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $proveedor = new Proveedor();
        $form = $this->createForm(ProveedorType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createProveedor($proveedor);
                $this->addFlash('success', 'El proveedor ha sido creado correctamente.');
                return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear el proveedor: ' . $e->getMessage());
            }
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
    public function edit(Request $request, Proveedor $proveedor): Response
    {
        $form = $this->createForm(ProveedorType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateProveedor($proveedor);
                $this->addFlash('success', 'El proveedor ha sido actualizado correctamente.');
                return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el proveedor: ' . $e->getMessage());
            }
        }

        return $this->render('proveedor/edit.html.twig', [
            'proveedor' => $proveedor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_proveedor_delete', methods: ['POST'])]
    public function delete(Request $request, Proveedor $proveedor): Response
    {
        if ($this->isCsrfTokenValid('delete' . $proveedor->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteProveedor($proveedor);
                $this->addFlash('success', 'El proveedor ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el proveedor.');
        }

        return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
    }
}
