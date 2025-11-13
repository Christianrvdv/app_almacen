<?php

namespace App\Controller;

use App\Entity\Proveedor;
use App\Form\ProveedorType;
use App\Service\Proveedor\Interface\ProveedorServiceInterface;
use App\Service\Proveedor\Interface\ProveedorQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proveedor')]
final class ProveedorController extends AbstractController
{
    public function __construct(
        private ProveedorQueryInterface $queryService,
        private ProveedorServiceInterface $operationsService
    ) {}

    #[Route(name: 'app_proveedor_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

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
        return $this->handleProveedorForm($request, $proveedor, 'create');
    }

    #[Route('/{id}/edit', name: 'app_proveedor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proveedor $proveedor): Response
    {
        return $this->handleProveedorForm($request, $proveedor, 'update');
    }

    private function handleProveedorForm(Request $request, Proveedor $proveedor, string $operation): Response
    {
        $form = $this->createForm(ProveedorType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->operationsService->create($proveedor);
                    $message = 'El proveedor ha sido creado correctamente.';
                    $redirectRoute = 'app_proveedor_index';
                } else {
                    $this->operationsService->update($proveedor);
                    $message = 'El proveedor ha sido actualizado correctamente.';
                    $redirectRoute = 'app_proveedor_show';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute(
                    $redirectRoute,
                    $operation === 'update' ? ['id' => $proveedor->getId()] : [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar el proveedor: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("proveedor/{$template}", [
            'proveedor' => $proveedor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_proveedor_delete', methods: ['POST'])]
    public function delete(Request $request, Proveedor $proveedor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$proveedor->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->delete($proveedor);
                $this->addFlash('success', 'El proveedor ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el proveedor.');
        }

        return $this->redirectToRoute('app_proveedor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_proveedor_show', methods: ['GET'])]
    public function show(Proveedor $proveedor): Response
    {
        return $this->render('proveedor/show.html.twig', [
            'proveedor' => $proveedor,
        ]);
    }
}
