<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Form\ClienteType;
use App\Repository\ClienteRepository;
use App\Service\Cliente\Interface\ClienteOperationsInterface;
use App\Service\Cliente\Interface\ClienteSearchInterface;
use App\Service\Cliente\Interface\ClienteStatsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cliente')]
final class ClienteController extends AbstractController
{
    public function __construct(
        private ClienteSearchInterface $searchService,
        private ClienteStatsInterface $statsService,
        private ClienteOperationsInterface $operationsService
    ) {}

    #[Route(name: 'app_cliente_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->searchService->searchAndPaginate($request);
        $statistics = $this->statsService->getStatistics();

        return $this->render('cliente/index.html.twig', [
            'clientes' => $searchResult['pagination'],
            'totalClientes' => $statistics['totalClientes'],
            'totalConEmail' => $statistics['totalConEmail'],
            'totalConTelefono' => $statistics['totalConTelefono'],
            'totalConDireccion' => $statistics['totalConDireccion'],
            'searchTerm' => $searchResult['searchTerm'],
        ]);
    }

    #[Route('/new', name: 'app_cliente_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $cliente = new Cliente();
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->createCliente($cliente);
                $this->addFlash('success', 'El cliente ha sido creado correctamente.');
                return $this->redirectToRoute('app_cliente_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear el cliente: ' . $e->getMessage());
            }
        }

        return $this->render('cliente/new.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cliente_show', methods: ['GET'])]
    public function show(ClienteRepository $clienteRepository, Cliente $cliente): Response
    {
        $deuda = $clienteRepository->findDeudaTotalByCliente($cliente);
        return $this->render('cliente/show.html.twig', [
            'cliente' => $cliente,
            'deuda' => $deuda,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cliente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cliente $cliente): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operationsService->updateCliente($cliente);
                $this->addFlash('success', 'El cliente ha sido actualizado correctamente.');
                return $this->redirectToRoute('app_cliente_show', ['id' => $cliente->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar el cliente: ' . $e->getMessage());
            }
        }

        return $this->render('cliente/edit.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cliente_delete', methods: ['POST'])]
    public function delete(Request $request, Cliente $cliente): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cliente->getId()->toRfc4122(), $request->getPayload()->getString('_token'))) {
            try {
                $this->operationsService->deleteCliente($cliente);
                $this->addFlash('success', 'El cliente ha sido eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el cliente: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el cliente.');
        }

        return $this->redirectToRoute('app_cliente_index', [], Response::HTTP_SEE_OTHER);
    }
}
