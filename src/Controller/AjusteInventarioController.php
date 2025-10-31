<?php

namespace App\Controller;

use App\Entity\AjusteInventario;
use App\Entity\Producto;
use App\Form\AjusteInventarioType;
use App\Repository\AjusteInventarioRepository;
use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ajuste/inventario')]
final class AjusteInventarioController extends AbstractController
{
    public function __construct(
        private CommonService $commonService
    ) {}

    #[Route(name: 'app_ajuste_inventario_index', methods: ['GET'])]
    public function index(AjusteInventarioRepository $ajusteInventarioRepository): Response
    {
        $ajuste_inventarios = $ajusteInventarioRepository->findAll();

        // Calcular la cantidad de usuarios únicos
        $usuariosUnicos = [];
        foreach ($ajuste_inventarios as $ajuste) {
            $usuariosUnicos[] = $ajuste->getUsuario();
        }
        $cantidadUsuariosUnicos = count(array_unique($usuariosUnicos));

        return $this->render('ajuste_inventario/index.html.twig', [
            'ajuste_inventarios' => $ajuste_inventarios,
            'cantidad_usuarios_unicos' => $cantidadUsuariosUnicos,
        ]);
    }

    /**
     * Método privado para manejar el formulario de ajuste (elimina duplicación)
     */
    private function handleAjusteForm(
        Request $request,
        EntityManagerInterface $entityManager,
        AjusteInventario $ajusteInventario
    ): Response {
        $form = $this->createForm(AjusteInventarioType::class, $ajusteInventario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ajusteInventario);
            $entityManager->flush();

            $this->addFlash('success', 'El ajuste de inventario ha sido creado correctamente.');

            return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ajuste_inventario/new.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_ajuste_inventario_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ajusteInventario = new AjusteInventario();
        $ajusteInventario->setFecha($this->commonService->getCurrentDateTime());
        $ajusteInventario->setUsuario($this->commonService->getCurrentUsername());

        return $this->handleAjusteForm($request, $entityManager, $ajusteInventario);
    }

    #[Route('/new/{id}', name: 'app_ajuste_inventario_new_by_id', methods: ['GET', 'POST'])]
    public function newById(Request $request, EntityManagerInterface $entityManager, Producto $producto): Response
    {
        $ajusteInventario = new AjusteInventario();
        $ajusteInventario->setFecha($this->commonService->getCurrentDateTime());
        $ajusteInventario->setUsuario($this->commonService->getCurrentUsername());
        $ajusteInventario->setProducto($producto);

        return $this->handleAjusteForm($request, $entityManager, $ajusteInventario);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_show', methods: ['GET'])]
    public function show(AjusteInventario $ajusteInventario): Response
    {
        return $this->render('ajuste_inventario/show.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ajuste_inventario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AjusteInventario $ajusteInventario, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AjusteInventarioType::class, $ajusteInventario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'El ajuste de inventario ha sido actualizado correctamente.');

            return $this->redirectToRoute('app_ajuste_inventario_show', [
                'id' => $ajusteInventario->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ajuste_inventario/edit.html.twig', [
            'ajuste_inventario' => $ajusteInventario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ajuste_inventario_delete', methods: ['POST'])]
    public function delete(Request $request, AjusteInventario $ajusteInventario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ajusteInventario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ajusteInventario);
            $entityManager->flush();

            $this->addFlash('success', 'El ajuste de inventario ha sido eliminado correctamente.');
        } else {
            $this->addFlash('error', 'Error de seguridad. No se pudo eliminar el ajuste de inventario.');
        }

        return $this->redirectToRoute('app_ajuste_inventario_index', [], Response::HTTP_SEE_OTHER);
    }
}
