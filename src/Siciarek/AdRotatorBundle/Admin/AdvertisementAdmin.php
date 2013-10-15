<?php

namespace Siciarek\AdRotatorBundle\Admin;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Siciarek\AdRotatorBundle\Form\Type\DatePickerType;
use Siciarek\AdRotatorBundle\Entity\Advertisement;
use Siciarek\AdRotatorBundle\Entity\AdvertisementType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdvertisementAdmin extends DefaultAdmin
{
    protected function configureRoutes(RouteCollection $collection) {

        /**
         * @var EntityManager $em
         */
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.entity_manager');

        $types_count = intval($em->getRepository('SiciarekAdRotatorBundle:AdvertisementType')
            ->createNamedQuery('count')
            ->getSingleScalarResult())
        ;

        if($types_count == 0) {
            // $collection->clearExcept(array('list'));
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $img = '';

        if ($this->getSubject()->getId()) {
            $path = $this->getSubject()->getPath();
            if (preg_match('|^https?://|', $path)) {
                $src = $path;
            } else {
                $temp = array(
                    $this->getRequest()->getSchemeAndHttpHost(),
                    $this->getSubject()->getWebPath(),
                );
                $src = implode('/', $temp);
            }

            if(preg_match('/\.swf$/', $src) > 0) {
                $params = $this->getSubject()->getType()->getDefinition();
                $width = $params['width'];
                $height = $params['height'];

$fmt =<<<IMG
<object width="%d" height="%d" align="middle" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">
    <param name="allowFullScreen" value="false">
    <param name="movie" value="%s">
    <param name="quality" value="high">
    <param name="scale" value="noscale">
    <param name="salign" value="lt">
    <param name="wmode" value="opaque">
    <param name="allowScriptAccess" value="never">
    <embed width="%d" height="%d" align="middle" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowscriptaccess="never" allowfullscreen="false" salign="LT" scale="noscale" wmode="opaque" quality="high" src="%s"/>
</object>
IMG;
                $img = sprintf($fmt, $width, $height, $src, $width, $height, $src);

            }
            else {
                $img = sprintf('<img style="border:1px solid silver" src="%s"/>', $src);
            }
        }

        $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.entity_manager');

        $type_id = $this->getRequest()->get('type', 0);

        if ($type_id === 0) {
            if ($this->getSubject()->getId() === null) {
                $types = $em->getRepository('SiciarekAdRotatorBundle:AdvertisementType')->findAll();
                $type_id = count($types) > 0 ? $types[0]->getId() : 0;
            } else {
                $type_id = $this->getSubject()->getType()->getId();
            }
        }


        $option_options = array(
            'label' => 'sale.option',
            'required' => true,
            'expanded' => true,
            'multiple' => false,
        );

        $preferred_type = $em->getRepository('SiciarekAdRotatorBundle:AdvertisementType')->find($type_id);

        if ($preferred_type instanceof AdvertisementType) {
            $option_options['choices'] = $preferred_type->getPrices();
        }

        $type_onchange = sprintf('var url=location.href.replace(/\?.*$/, "")+"?type="+this.value;location.href=url');

        $option = 0;

        if ($this->getSubject()->getId() === null) {
            $type_options['preferred_choices'] = array($preferred_type);
        } else {
            $this->getSubject()->setType($preferred_type);
        }

        $formMapper
            ->with('tabs.sale.sale')
                ->add('enabled', null, array(
                    'label' => 'sale.enabled',
                    'required' => false,
                ))
                ->add('type', null, array(
                    'label' => 'sale.type',
                    'required' => true,
                    'attr' => array(
                        'onchange' => $type_onchange,
                    )
                ))
                ->add('option', null, $option_options)
                ->add('client', 'sonata_type_model', array(
                    'label' => 'sale.client',
                    'required' => true,
                    'empty_value' => 'Select from list',
                ))
                ->add('title', null, array(
                    'label' => 'sale.title',
                    'trim' => true,
                    'required' => false,
                    'help' => 'Jeżeli tytuł nie zostanie podany wprowadzona zostanie nazwa klienta.'
                ))
                ->add('leads_to', 'url', array(
                    'label' => 'sale.leads_to',
                    'trim' => true,
                    'required' => false
                ))
                ->add('uploaded_file', 'file', array(
                    'label' => 'sale.file',
                    'required' => false,
                    'help' => $img,
                ))

            ->with('tabs.sale.displaying_params')
                ->add('exclusive', null, array(
                    'label' => 'sale.exclusive',
                    'required' => false,
                ))
                ->add('everlasting', null, array(
                    'label' => 'sale.everlasting',
                    'required' => false,
                ))
                ->add('starts_at', new DatePickerType(), array(
                    'label' => 'sale.starts_at',
                    'required' => false,
                ))

            ->with('tabs.sale.url')
                ->add('path', null, array(
                    'label' => 'sale.path',
                    'trim' => true,
                    'required' => false,
                    'help' => 'help.sale.path',
                ));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('title')
            ->add('displayed')
            ->add('clicked')
            ->add('client')
            ->add('type')
            ->add('starts_at')
            ->add('expires_at')
            ->add('price')
            ->add('enabled', null, array('editable' => true))
            ->add('exclusive', null, array('editable' => true))
            ->add('everlasting', null, array('editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array()
                ),
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add("client")
            ->add("type");
    }

    public function prePersist($object)
    {
        $this->updateObject($object);
    }

    public function preUpdate($object)
    {
        $this->updateObject($object);
    }

    public function updateObject(Advertisement $object)
    {
        $path = $object->getPath();

        if (!preg_match('|^https?://|', $path)) {
            $object->upload('setPath');
        }
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        /**
         * @var UploadedFile $file
         */
        $file = $object->getUploadedFile();

        if (!($file instanceof UploadedFile) and $object->getPath() === null) {
            $errorElement->addViolation('Nid podano pliku graficznego ani odnośnika.');
        }

        if ($file instanceof UploadedFile) {

            $def = $object->getType()->getDefinition();
            $extensions = explode(';', $def['formats']);
            $width = $def['width'];
            $height = $def['height'];

            $ext = $file->getClientOriginalExtension();

            // Sprawdzenie rozszerzenia:
            if (!in_array($ext, $extensions)) {
                $errorElement->addViolation("Obsługiwane rozszerzenia (" . implode(', ', $extensions) . "), ten plik ma (${ext}).");
                return;
            }

            $bin = $file->getRealPath();
            $imageinfo = getimagesize($bin);

            if ($ext === 'svg') {
                // @ - Przechwycenie ostrzerzenia o niewłaściwym formacie pliku:
                @$xml = simplexml_load_file($bin);

                if ($xml instanceof \SimpleXMLElement) {

                    $width = intval($xml->attributes()->width);
                    $height = intval($xml->attributes()->height);

                    $imageinfo = array(
                        $width,
                        $height,
                        IMAGETYPE_UNKNOWN,
                        sprintf('width="%d" height="%d"', $width, $height),
                        'mime' => 'image/svg+xml',
                    );
                } else {
                    $imageinfo = false;
                }
            }

            if (!is_array($imageinfo)) {
                $msg = sprintf('Niewłaściwa zawartość pliku.');
                $errorElement->addViolation($msg);
                return;
            }

            $expected = array($width, $height);
            $given = array($imageinfo[0], $imageinfo[1]);

            // Sprawdzenie rozmiarów
            if ($expected !== $given) {
                $msg = sprintf('Wymagany rozmiar (%dx%d), ten plik ma (%dx%d).', $width, $height, $imageinfo[0], $imageinfo[1]);
                $errorElement->addViolation($msg);
            }
        }
    }
}