<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Acl\Communication\Controller;

use Generated\Shared\Transfer\RoleTransfer;
use Generated\Shared\Transfer\RuleTransfer;
use Spryker\Zed\Acl\Business\Exception\RoleNameExistsException;
use Spryker\Zed\Acl\Business\Exception\RootNodeModificationException;
use Spryker\Zed\Acl\Communication\Form\RoleForm;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @method \Spryker\Zed\Acl\Communication\AclCommunicationFactory getFactory()
 * @method \Spryker\Zed\Acl\Business\AclFacadeInterface getFacade()
 * @method \Spryker\Zed\Acl\Persistence\AclQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Acl\Persistence\AclRepositoryInterface getRepository()
 */
class RoleController extends AbstractController
{
    /**
     * @var string
     */
    public const PARAM_ID_ROLE = 'id-role';

    /**
     * @var string
     */
    public const ACL_ROLE_LIST_URL = '/acl/role/index';

    /**
     * @var string
     */
    public const ROLE_UPDATE_URL = '/acl/role/update?id-role=%d';

    /**
     * @var string
     */
    protected const MESSAGE_ROLE_NOT_FOUND = "Role couldn't be found";

    /**
     * @return array
     */
    public function indexAction()
    {
        $roleTable = $this->getFactory()->createRoleTable();

        return $this->viewResponse([
            'roleTable' => $roleTable->render(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function tableAction()
    {
        $roleTable = $this->getFactory()->createRoleTable();

        return $this->jsonResponse(
            $roleTable->fetchData(),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function createAction(Request $request)
    {
        $ruleForm = $this->getFactory()
            ->createRoleForm()
            ->handleRequest($request);

        if ($ruleForm->isSubmitted() && $ruleForm->isValid()) {
            $formData = $ruleForm->getData();

            try {
                $roleTransfer = $this->getFacade()->addRole($formData[RoleForm::FIELD_NAME]);

                $this->addSuccessMessage(
                    'Role "%s" successfully added.',
                    ['%s' => $formData[RoleForm::FIELD_NAME]],
                );

                return $this->redirectResponse(
                    sprintf(static::ROLE_UPDATE_URL, $roleTransfer->getIdAclRole()),
                );
            } catch (RoleNameExistsException $e) {
                $this->addErrorMessage($e->getMessage());
            } catch (RootNodeModificationException $e) {
                $this->addErrorMessage($e->getMessage());
            }
        }

        return $this->viewResponse([
            'roleForm' => $ruleForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function updateAction(Request $request)
    {
        $idAclRole = $this->castId($request->query->get(static::PARAM_ID_ROLE));

        if (!$idAclRole) {
            $this->addErrorMessage('Missing role id!');

            return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
        }

        $dataProvider = $this->getFactory()->createAclRoleFormDataProvider();
        $formData = $dataProvider->getData($idAclRole);

        if (!$formData) {
            $this->addErrorMessage(static::MESSAGE_ROLE_NOT_FOUND);

            return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
        }

        $roleForm = $this->getFactory()
            ->createRoleForm($formData)
            ->handleRequest($request);

        $this->handleRoleForm($request, $roleForm);

        $ruleSetForm = $this->createAndHandleRuleSetForm($request, $idAclRole);
        if ($ruleSetForm->isSubmitted() && $ruleSetForm->isValid()) {
            return $this->redirectResponse(sprintf(static::ROLE_UPDATE_URL, $idAclRole));
        }

        $ruleSetTable = $this->getFactory()->createRulesetTable($idAclRole);

        return [
            'roleForm' => $roleForm->createView(),
            'ruleSetForm' => $ruleSetForm->createView(),
            'ruleSetTable' => $ruleSetTable->render(),
            'roleTransfer' => $this->getFacade()->findRoleById($idAclRole),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_DELETE)) {
            throw new MethodNotAllowedHttpException([Request::METHOD_DELETE], 'This action requires a DELETE request.');
        }

        $form = $this->getFactory()->createDeleteRoleForm()->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorMessage('CSRF token is not valid');

            return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
        }

        $idRole = $this->castId($request->request->get(static::PARAM_ID_ROLE));

        if (!$idRole) {
            $this->addErrorMessage('Missing role id!');

            return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
        }

        $groupsHavingThisRole = $this->getQueryContainer()->queryRoleHasGroup($idRole)->count();
        if ($groupsHavingThisRole > 0) {
            $this->addErrorMessage('Unable to delete because role has groups assigned.');

            return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
        }

        $this->getFacade()->removeRole($idRole);
        $this->addSuccessMessage('Role was successfully removed.');

        return $this->redirectResponse(static::ACL_ROLE_LIST_URL);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function rulesetTableAction(Request $request)
    {
        $idRole = $this->castId($request->get(static::PARAM_ID_ROLE));
        $ruleSetTable = $this->getFactory()->createRulesetTable($idRole);

        return $this->jsonResponse(
            $ruleSetTable->fetchData(),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $idAclRole
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createAndHandleRuleSetForm(Request $request, $idAclRole)
    {
        $dataProvider = $this->getFactory()->createAclRuleFormDataProvider();

        $ruleSetForm = $this->getFactory()
            ->createRuleForm(
                $dataProvider->getData($idAclRole),
                array_merge(
                    $dataProvider->getOptions(),
                    $dataProvider->getRouterOptions(
                        $request->get('ruleset')['bundle'] ?? null,
                        $request->get('ruleset')['controller'] ?? null,
                    ),
                ),
            )
            ->handleRequest($request);

        if ($ruleSetForm->isSubmitted() && $ruleSetForm->isValid()) {
            $ruleTransfer = new RuleTransfer();
            $ruleTransfer = $ruleTransfer->fromArray($ruleSetForm->getData());

            $ruleTransfer = $this->getFacade()->addRule($ruleTransfer);

            if ($ruleTransfer->getIdAclRule()) {
                $this->addSuccessMessage('Rule successfully added.');
            } else {
                $this->addErrorMessage('Failed to add Rule.');
            }
        }

        return $ruleSetForm;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormInterface $roleForm
     *
     * @return void
     */
    protected function handleRoleForm(Request $request, FormInterface $roleForm)
    {
        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            $formData = $roleForm->getData();

            $roleTransfer = new RoleTransfer();
            $roleTransfer->fromArray($formData);

            try {
                $this->getFacade()->updateRole($roleTransfer);
                $this->addSuccessMessage(
                    'Role "%s" successfully updated.',
                    ['%s' => $roleTransfer->getName()],
                );
            } catch (RoleNameExistsException $e) {
                $this->addErrorMessage($e->getMessage());
            } catch (RootNodeModificationException $e) {
                $this->addErrorMessage($e->getMessage());
            }
        }
    }
}
