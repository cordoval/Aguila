<?php

namespace Storm\AguilaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Storm\AguilaBundle\Entity\Project;
use Storm\AguilaBundle\Entity\Feature;
use Storm\AguilaBundle\Form\FeatureType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Feature controller.
 *
 * @Route("/{project_slug}")
 */
class FeatureController extends Controller
{
    /**
     * Lists all Feature features.
     *
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $features = $em->getRepository('AguilaBundle:Feature')->findAll();

        return array('features' => $features);
    }

    /**
     * Finds and displays a Feature feature.
     *
     * @Route("/{slug}", name="aguila_feature_show")
     * @Method("get")
     * @Template()
     * @ParamConverter("feature", class="AguilaBundle:Feature", options={"method"="findFeatureBySlugs", "params"={"project_slug", "slug"}})
     */
    public function showAction(Feature $feature)
    {
        $this->checkAccess('VIEW', $feature->getProject());

        return array(
            'feature' => $feature,
        );
    }

    /**
     * Displays a form to create a new Feature.
     *
     * @Template()
     * @ParamConverter("project", class="AguilaBundle:Project", options={"method"="findOneBySlug", "params"={"project_slug"}})
     */
    public function newAction(Project $project)
    {
        $feature = new Feature();
        $form   = $this->createForm(new FeatureType(), $feature);

        return array(
            'feature' => $feature,
            'form'   => $form->createView(),
            'project_slug' => $project->getSlug(),
        );
    }

    /**
     * Creates a new Feature.
     *
     * @Route("/feature/create", name="aguila_feature_create")
     * @Method("post")
     * @Template("AguilaBundle:Feature:new.html.twig")
     * @ParamConverter("project", class="AguilaBundle:Project", options={"method"="findOneBySlug", "params"={"project_slug"}})
     */
    public function createAction(Project $project)
    {
        $this->checkAccess('EDIT', $project);

        $feature  = new Feature();
        $request = $this->getRequest();
        $form    = $this->createForm(new FeatureType(), $feature);
        $form->bindRequest($request);

        if ($form->isValid()) {

            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getDoctrine()->getEntityManager();

            $feature->setProject($project);

            $em->persist($feature);
            $em->flush();

            return $this->redirect($this->generateUrl('aguila_feature_show', array(
                'project_slug' => $project->getSlug(),
                'slug' => $feature->getSlug()
            )));
        }

        return array(
            'feature' => $feature,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Feature feature.
     *
     * @Route("/feature/{slug}/edit", name="aguila_feature_edit")
     * @Template()
     * @ParamConverter("slug", class="AguilaBundle:Feature")
     * @ParamConverter("feature", class="AguilaBundle:Feature", options={"method"="findFeatureBySlugs", "params"={"project_slug", "slug"}})
     */
    public function editAction(Feature $feature)
    {
        $this->checkAccess('EDIT', $feature->getProject());

        $editForm = $this->createForm(new FeatureType(), $feature);
        $deleteForm = $this->createDeleteForm($feature->getSlug());

        return array(
            'feature'     => $feature,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Feature feature.
     *
     * @Route("/feature/{slug}/update", name="aguila_feature_update")
     * @Method("post")
     * @Template("AguilaBundle:Feature:edit.html.twig")
     * @ParamConverter("feature", class="AguilaBundle:Feature", options={"method"="findFeatureBySlugs", "params"={"project_slug", "slug"}})
     */
    public function updateAction(Feature $feature)
    {
        $this->checkAccess('EDIT', $feature->getProject());

        $editForm   = $this->createForm(new FeatureType(), $feature);
        $deleteForm = $this->createDeleteForm($feature->getSlug());

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($feature);
            $em->flush();

            return $this->redirect($this->generateUrl('aguila_feature_show', array(
                'project_slug' => $feature->getProject()->getSlug(),
                'slug' => $feature->getSlug(),
            )));
        }

        return array(
            'feature'     => $feature,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Feature feature.
     *
     * @Route("/feature/{slug}/delete", name="aguila_feature_delete")
     * @Method("post")
     * @ParamConverter("feature", class="AguilaBundle:Feature", options={"method"="findFeatureBySlugs", "params"={"project_slug", "slug"}})
     */
    public function deleteAction(Feature $feature)
    {
        $this->checkAccess('EDIT', $feature->getProject());

        $form = $this->createDeleteForm($feature->getSlug());
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            $em->remove($feature);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('aguila_feature_show', array(
            'project_slug' => $feature->getProject()->getSlug(),
            'slug' => $feature->getSlug(),
        )));
    }

    private function createDeleteForm($slug)
    {
        return $this->createFormBuilder(array('slug' => $slug))
            ->add('slug', 'hidden')
            ->getForm()
        ;
    }
}
