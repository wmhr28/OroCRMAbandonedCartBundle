<?php

namespace OroCRM\Bundle\AbandonedCartBundle\Controller;

use OroCRM\Bundle\AbandonedCartBundle\Entity\AbandonedCartConversion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @Route("/abandoned-cart-conversion")
 */
class AbandonedCartConversionController extends Controller
{
    /**
     * @Route(
     *      "/enable/{entity}",
     *      name="orocrm_abandoned_cart_enable_conversion",
     *      requirements={"entity"="\d+"}
     * )
     * @ParamConverter(
     *      "marketingList",
     *      class="OroCRMMarketingListBundle:MarketingList",
     *      options={"id" = "entity"}
     * )
     * @AclAncestor("orocrm_mailchimp")
     *
     * @Template
     *
     * @param MarketingList $marketingList
     * @return array
     */
    public function conversionButtonAction(MarketingList $marketingList)
    {
        return [
            'marketingList' => $marketingList
        ];
    }

    /**
     * @Route(
     *      "/manage-workflow/{id}",
     *      name="orocrm_abandoned_cart_manage_workflow",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template
     * @param MarketingList $marketingList
     * @return array
     */
    public function manageWorkflowAction(MarketingList $marketingList)
    {
        $conversion = $this->getConversionByMarketingList($marketingList);

        /** @var Form $form */
        $form = $this->get('orocrm_mailchimp.form.abandonedcart_list_conversion');

        $handler = $this->get('orocrm_mailchimp.form.handler.conversion_form');

        $result = ['entity' => $conversion];
        if ($handler->process($conversion)) {
            $result['savedId'] = $conversion->getId();
        }

        $result['form'] = $form->createView();

        return $result;
    }

    /**
     * @param MarketingList $marketingList
     * @return AbandonedCartConversion
     */
    protected function getConversionByMarketingList(MarketingList $marketingList)
    {
        $conversion = $this->get('orocrm_abandonedcart.conversion_manager')->findConversionByMarketingList($marketingList);

        if (!$conversion) {
            $conversion = $this->get('orocrm_abandonedcart_list.conversion_factory')->create($marketingList);
        }

        return $conversion;
    }
}
