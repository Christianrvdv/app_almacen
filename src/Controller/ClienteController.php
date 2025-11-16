<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Form\ClienteType;
use App\Repository\ClienteRepository;
use App\Service\Cliente\Interface\ClienteQueryInterface;
use App\Service\Cliente\Interface\ClienteServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cliente')]
final class ClienteController extends AbstractController
{
    public function __construct(
        private ClienteQueryInterface $queryService,
        private ClienteServiceInterface $clienteService
    ) {}

    #[Route(name: 'app_cliente_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchResult = $this->queryService->searchAndPaginate($request);
        $statistics = $this->queryService->getStatistics();

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
        return $this->handleClienteForm($request, $cliente, 'create');
    }

    #[Route('/{id}/edit', name: 'app_cliente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cliente $cliente): Response
    {
        return $this->handleClienteForm($request, $cliente, 'update');
    }

    private function handleClienteForm(Request $request, Cliente $cliente, string $operation): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($operation === 'create') {
                    $this->clienteService->create($cliente);
                    $message = 'El cliente ha sido creado correctamente.';
                    $redirectRoute = 'app_cliente_index';
                } else {
                    $this->clienteService->update($cliente);
                    $message = 'El cliente ha sido actualizado correctamente.';
                    $redirectRoute = 'app_cliente_show';
                }

                $this->addFlash('success', $message);
                return $this->redirectToRoute(
                    $redirectRoute,
                    $operation === 'update' ? ['id' => $cliente->getId()] : [],
                    Response::HTTP_SEE_OTHER
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar el cliente: ' . $e->getMessage());
            }
        }

        $template = $operation === 'create' ? 'new.html.twig' : 'edit.html.twig';
        return $this->render("cliente/{$template}", [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cliente_show', methods: ['GET'])]
    public function show(ClienteRepository $clienteRepository, Cliente $cliente): Response
    {
        $deuda = $clienteRepository->findDeudaTotalByCliente($cliente);
        $comprasTotalesCompletadas = $clienteRepository->findComprasTotalesCompletadasByCliente($cliente);

        return $this->render('cliente/show.html.twig', [
            'cliente' => $cliente,
            'deuda' => $deuda,
            'compras_totales_completadas' => $comprasTotalesCompletadas,
        ]);
    }

    #[Route('/{id}', name: 'app_cliente_delete', methods: ['POST'])]
    public function delete(Request $request, Cliente $cliente): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cliente->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $this->clienteService->delete($cliente);
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
