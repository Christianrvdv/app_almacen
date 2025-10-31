<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Form\ClienteType;
use App\Repository\ClienteRepository;
use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cliente')]
final class ClienteController extends AbstractController
{
    public function __construct(
        private CommonService $commonService
    )
    {
    }

    #[Route(name: 'app_cliente_index', methods: ['GET'])]
    public function index(Request $request, ClienteRepository $clienteRepository, PaginatorInterface $paginator): Response
    {
        $query = $clienteRepository->createQueryBuilder('c')
            ->orderBy('c.nombre', 'ASC')
            ->getQuery();

        $clientes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Estadísticas totales
        $totalClientes = $clienteRepository->count([]);
        $totalConEmail = $clienteRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConTelefono = $clienteRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.telefono IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $totalConDireccion = $clienteRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.direccion IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('cliente/index.html.twig', [
            'clientes' => $clientes,
            'totalClientes' => $totalClientes,
            'totalConEmail' => $totalConEmail,
            'totalConTelefono' => $totalConTelefono,
            'totalConDireccion' => $totalConDireccion,
        ]);
    }

    #[Route('/new', name: 'app_cliente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cliente = new Cliente();

        // Usar CommonService para valores por defecto
        $cliente->setFechaRegistro($this->commonService->getCurrentDateTime());
        $cliente->setCompraTotales('0.00');

        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cliente);
            $entityManager->flush();

            $this->addFlash('success', 'El cliente ha sido creado correctamente.');
            return $this->redirectToRoute('app_cliente_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Cliente $cliente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'El cliente ha sido actualizado correctamente.');

            return $this->redirectToRoute('app_cliente_show', ['id' => $cliente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cliente/edit.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cliente_delete', methods: ['POST'])]
    public function delete(Request $request, Cliente $cliente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cliente->getId()->toRfc4122(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cliente);
            $entityManager->flush();

            $this->addFlash('success', 'El cliente ha sido eliminado correctamente.');
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el cliente.');
        }

        return $this->redirectToRoute('app_cliente_index', [], Response::HTTP_SEE_OTHER);
    }
}
