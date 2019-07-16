<?php
namespace Blackbox\Epace\Block\Adminhtml\Epace;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Blackbox\Epace\Model\epaceFactory
     */
    protected $_epaceFactory;

    /**
     * @var \Blackbox\Epace\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Blackbox\Epace\Model\epaceFactory $epaceFactory
     * @param \Blackbox\Epace\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Blackbox\Epace\Model\EpaceFactory $EpaceFactory,
        \Blackbox\Epace\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_epaceFactory = $EpaceFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_epaceFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'name',
					[
						'header' => __('Name'),
						'index' => 'name',
						
						
					]
				);
				
				$this->addColumn(
					'processed_time',
					[
						'header' => __('Processed Time'),
						'index' => 'processed_time',
						'type' => 'datetime'
						
					]
				);
				
				$this->addColumn(
					'status',
					[
						'header' => __('Status'),
						'index' => 'status',
						'type' => 'options',
						'options' => ['' => '','Critical' => 'Critical', 'With errors' => 'With errors'],
						
						
					]
				);
				
				$this->addColumn(
					'host',
					[
						'header' => __('Host'),
						'index' => 'host',
						
						
					]
				);
				
				$this->addColumn(
					'username',
					[
						'header' => __('Username'),
						'index' => 'username',
						
						
					]
				);
				
				$this->addColumn(
					'password',
					[
						'header' => __('Password'),
						'index' => 'password',
						
						
					]
				);
					
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('epace/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('epace/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Blackbox_Epace::epace/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('epace');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('epace/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('epace/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('epace/*/index', ['_current' => true]);
    }

    /**
     * @param \Blackbox\Epace\Model\epace|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'epace/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	

}