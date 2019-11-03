<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Pulse\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\AsynchronousImportCsvApi\Api\StartImportInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\AsynchronousImportSourceDataRetrieving\Model\Source as AsyncSource;
use Magento\AsynchronousImportDataExchanging\Model\Import as AsyncImporter;
use Magento\AsynchronousImportCsv\Model\CsvFormat;

class Import extends Command
{
    private $importer;

    private $om;

    public function __construct
    (
        StartImportInterface $importer,
        ObjectManagerInterface $objectManager,
        string $name = null)
    {
        $this->importer = $importer;
        $this->om = $objectManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName("magento:pulse:import");
        $this->setDescription("This command is a workaround for async import process");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Launching");
        $this->launch();
    }

    protected function launch()
    {
        $base64String = 'c2t1LHN0b3JlX3ZpZXdfY29kZSxhdHRyaWJ1dGVfc2V0X2NvZGUscHJvZHVjdF90eXBlLGNhdGVnb3JpZXMscHJvZHVjdF93ZWJzaXRlcyxuYW1lLGRlc2NyaXB0aW9uLHNob3J0X2Rlc2NyaXB0aW9uLHdlaWdodCxwcm9kdWN0X29ubGluZSx0YXhfY2xhc3NfbmFtZSx2aXNpYmlsaXR5LHByaWNlLHNwZWNpYWxfcHJpY2Usc3BlY2lhbF9wcmljZV9mcm9tX2RhdGUsc3BlY2lhbF9wcmljZV90b19kYXRlLHVybF9rZXksbWV0YV90aXRsZSxtZXRhX2tleXdvcmRzLG1ldGFfZGVzY3JpcHRpb24sYmFzZV9pbWFnZSxiYXNlX2ltYWdlX2xhYmVsLHNtYWxsX2ltYWdlLHNtYWxsX2ltYWdlX2xhYmVsLHRodW1ibmFpbF9pbWFnZSx0aHVtYm5haWxfaW1hZ2VfbGFiZWwsc3dhdGNoX2ltYWdlLHN3YXRjaF9pbWFnZV9sYWJlbCxjcmVhdGVkX2F0LHVwZGF0ZWRfYXQsbmV3X2Zyb21fZGF0ZSxuZXdfdG9fZGF0ZSxkaXNwbGF5X3Byb2R1Y3Rfb3B0aW9uc19pbixtYXBfcHJpY2UsbXNycF9wcmljZSxtYXBfZW5hYmxlZCxnaWZ0X21lc3NhZ2VfYXZhaWxhYmxlLGN1c3RvbV9kZXNpZ24sY3VzdG9tX2Rlc2lnbl9mcm9tLGN1c3RvbV9kZXNpZ25fdG8sY3VzdG9tX2xheW91dF91cGRhdGUscGFnZV9sYXlvdXQscHJvZHVjdF9vcHRpb25zX2NvbnRhaW5lcixtc3JwX2Rpc3BsYXlfYWN0dWFsX3ByaWNlX3R5cGUsY291bnRyeV9vZl9tYW51ZmFjdHVyZSxhZGRpdGlvbmFsX2F0dHJpYnV0ZXMscXR5LG91dF9vZl9zdG9ja19xdHksdXNlX2NvbmZpZ19taW5fcXR5LGlzX3F0eV9kZWNpbWFsLGFsbG93X2JhY2tvcmRlcnMsdXNlX2NvbmZpZ19iYWNrb3JkZXJzLG1pbl9jYXJ0X3F0eSx1c2VfY29uZmlnX21pbl9zYWxlX3F0eSxtYXhfY2FydF9xdHksdXNlX2NvbmZpZ19tYXhfc2FsZV9xdHksaXNfaW5fc3RvY2ssbm90aWZ5X29uX3N0b2NrX2JlbG93LHVzZV9jb25maWdfbm90aWZ5X3N0b2NrX3F0eSxtYW5hZ2Vfc3RvY2ssdXNlX2NvbmZpZ19tYW5hZ2Vfc3RvY2ssdXNlX2NvbmZpZ19xdHlfaW5jcmVtZW50cyxxdHlfaW5jcmVtZW50cyx1c2VfY29uZmlnX2VuYWJsZV9xdHlfaW5jLGVuYWJsZV9xdHlfaW5jcmVtZW50cyxpc19kZWNpbWFsX2RpdmlkZWQsd2Vic2l0ZV9pZCxyZWxhdGVkX3NrdXMscmVsYXRlZF9wb3NpdGlvbixjcm9zc3NlbGxfc2t1cyxjcm9zc3NlbGxfcG9zaXRpb24sdXBzZWxsX3NrdXMsdXBzZWxsX3Bvc2l0aW9uLGFkZGl0aW9uYWxfaW1hZ2VzLGFkZGl0aW9uYWxfaW1hZ2VfbGFiZWxzLGhpZGVfZnJvbV9wcm9kdWN0X3BhZ2UsY3VzdG9tX29wdGlvbnMsYnVuZGxlX3ByaWNlX3R5cGUsYnVuZGxlX3NrdV90eXBlLGJ1bmRsZV9wcmljZV92aWV3LGJ1bmRsZV93ZWlnaHRfdHlwZSxidW5kbGVfdmFsdWVzLGJ1bmRsZV9zaGlwbWVudF90eXBlLGNvbmZpZ3VyYWJsZV92YXJpYXRpb25zLGNvbmZpZ3VyYWJsZV92YXJpYXRpb25fbGFiZWxzLGFzc29jaWF0ZWRfc2t1cwo5MDEzMzUsLExpZGVycGFwZWwsc2ltcGxlLCxiYXNlLCJMb3RlIHBhcmtlciBuYXZpZGFkIDIwMTkgY29udGVuaWRvIDcgYm9saWdyYWZvcyBpbSAvIHVyYmFuIHN1cnRpZG9zICsgMiBib2xpZ3JhZm9zIGltIGNvcmUgb2JzZXF1aW8iLCwiTG90ZSBwYXJrZXIgbmF2aWRhZCAyMDE5IGNvbnRlbmlkbyA3IGJvbGlncmFmb3MgaW0gLyB1cmJhbiBzdXJ0aWRvcyArIDIgYm9saWdyYWZvcyBpbSBjb3JlIG9ic2VxdWlvIiw1LjAwMDAsMSwiVGF4YWJsZSBHb29kcyIsIkNhdGFsb2csIFNlYXJjaCIsMTAuMDAwMCw1LjAwMDAsMTAvMjcvMTksMTAvMzEvMTksOTAxMzM1LCwsLCwsLCwsLCwsIjEwLzExLzE5LCA0OjI4IEFNIiwiMTAvMjcvMTksIDg6NDUgQU0iLCwsIkJsb2NrIGFmdGVyIEluZm8gQ29sdW1uIiwsLCxObywsLCwsLCwiVXNlIGNvbmZpZyIsLGNvc3Q9Mi41MDAwLDUwLjAwMDAsMC4wMDAwLDEsMCwwLDEsMS4wMDAwLDEsMTAwMDAuMDAwMCwxLDEsMS4wMDAwLDEsMSwxLDEsMS4wMDAwLDEsMCwwLDAsLCwsLCwsIi85LzAvOTAxMzM1Z18zXzUuanBnLC85LzAvOTAxMzM1Z18zXzIuanBnLC85LzAvOTAxMzM1Zy5qcGcsLzkvMC85MDEzMzVnXzNfMy5qcGcsLzkvMC85MDEzMzVnXzNfNC5qcGcsLzkvMC85MDEzMzVnXzEuanBnLC85LzAvOTAxMzM1Z180LmpwZyIsIiwsLCwsLCIsLCwsLCwsLCwsLAo5MDEzMzUsZGVmYXVsdCxMaWRlcnBhcGVsLHNpbXBsZSwsLCwsLCwsLCwsLCwsLCwsLC85LzAvOTAxMzM1Z180LmpwZywsLzkvMC85MDEzMzVnXzQuanBnLCwvOS8wLzkwMTMzNWdfNC5qcGcsLG5vX3NlbGVjdGlvbiwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCws';
        $sourceDefinition = '[
  {
    "sku": 901335,
    "store_view_code": "",
    "attribute_set_code": "Liderpapel",
    "product_type": "simple",
    "categories": "",
    "product_websites": "base",
    "name": "Lote parker navidad 2019 contenido 7 boligrafos im / urban surtidos + 2 boligrafos im core obsequio",
    "description": "",
    "short_description": "Lote parker navidad 2019 contenido 7 boligrafos im / urban surtidos + 2 boligrafos im core obsequio",
    "weight": 5,
    "product_online": 1,
    "tax_class_name": "Taxable Goods",
    "visibility": "Catalog, Search",
    "price": 10,
    "special_price": 5,
    "special_price_from_date": "10/27/19",
    "special_price_to_date": "10/31/19",
    "url_key": 901335,
    "meta_title": "",
    "meta_keywords": "",
    "meta_description": "",
    "base_image": "",
    "base_image_label": "",
    "small_image": "",
    "small_image_label": "",
    "thumbnail_image": "",
    "thumbnail_image_label": "",
    "swatch_image": "",
    "swatch_image_label": "",
    "created_at": "10/11/19, 4:28 AM",
    "updated_at": "10/27/19, 8:45 AM",
    "new_from_date": "",
    "new_to_date": "",
    "display_product_options_in": "Block after Info Column",
    "map_price": "",
    "msrp_price": "",
    "map_enabled": "",
    "gift_message_available": "No",
    "custom_design": "",
    "custom_design_from": "",
    "custom_design_to": "",
    "custom_layout_update": "",
    "page_layout": "",
    "product_options_container": "",
    "msrp_display_actual_price_type": "Use config",
    "country_of_manufacture": "",
    "additional_attributes": "cost=2.5000",
    "qty": 50,
    "out_of_stock_qty": 0,
    "use_config_min_qty": 1,
    "is_qty_decimal": 0,
    "allow_backorders": 0,
    "use_config_backorders": 1,
    "min_cart_qty": 1,
    "use_config_min_sale_qty": 1,
    "max_cart_qty": 10000,
    "use_config_max_sale_qty": 1,
    "is_in_stock": 1,
    "notify_on_stock_below": 1,
    "use_config_notify_stock_qty": 1,
    "manage_stock": 1,
    "use_config_manage_stock": 1,
    "use_config_qty_increments": 1,
    "qty_increments": 1,
    "use_config_enable_qty_inc": 1,
    "enable_qty_increments": 0,
    "is_decimal_divided": 0,
    "website_id": 0,
    "related_skus": "",
    "related_position": "",
    "crosssell_skus": "",
    "crosssell_position": "",
    "upsell_skus": "",
    "upsell_position": "",
    "additional_images": "/9/0/901335g_3_5.jpg,/9/0/901335g_3_2.jpg,/9/0/901335g.jpg,/9/0/901335g_3_3.jpg,/9/0/901335g_3_4.jpg,/9/0/901335g_1.jpg,/9/0/901335g_4.jpg",
    "additional_image_labels": ",,,,,,",
    "hide_from_product_page": "",
    "custom_options": "",
    "bundle_price_type": "",
    "bundle_sku_type": "",
    "bundle_price_view": "",
    "bundle_weight_type": "",
    "bundle_values": "",
    "bundle_shipment_type": "",
    "configurable_variations": "",
    "configurable_variation_labels": "",
    "associated_skus": ""
  },
  {
    "sku": 901335,
    "store_view_code": "default",
    "attribute_set_code": "Liderpapel",
    "product_type": "simple",
    "categories": "",
    "product_websites": "",
    "name": "",
    "description": "",
    "short_description": "",
    "weight": "",
    "product_online": "",
    "tax_class_name": "",
    "visibility": "",
    "price": "",
    "special_price": "",
    "special_price_from_date": "",
    "special_price_to_date": "",
    "url_key": "",
    "meta_title": "",
    "meta_keywords": "",
    "meta_description": "",
    "base_image": "/9/0/901335g_4.jpg",
    "base_image_label": "",
    "small_image": "/9/0/901335g_4.jpg",
    "small_image_label": "",
    "thumbnail_image": "/9/0/901335g_4.jpg",
    "thumbnail_image_label": "",
    "swatch_image": "no_selection",
    "swatch_image_label": "",
    "created_at": "",
    "updated_at": "",
    "new_from_date": "",
    "new_to_date": "",
    "display_product_options_in": "",
    "map_price": "",
    "msrp_price": "",
    "map_enabled": "",
    "gift_message_available": "",
    "custom_design": "",
    "custom_design_from": "",
    "custom_design_to": "",
    "custom_layout_update": "",
    "page_layout": "",
    "product_options_container": "",
    "msrp_display_actual_price_type": "",
    "country_of_manufacture": "",
    "additional_attributes": "",
    "qty": "",
    "out_of_stock_qty": "",
    "use_config_min_qty": "",
    "is_qty_decimal": "",
    "allow_backorders": "",
    "use_config_backorders": "",
    "min_cart_qty": "",
    "use_config_min_sale_qty": "",
    "max_cart_qty": "",
    "use_config_max_sale_qty": "",
    "is_in_stock": "",
    "notify_on_stock_below": "",
    "use_config_notify_stock_qty": "",
    "manage_stock": "",
    "use_config_manage_stock": "",
    "use_config_qty_increments": "",
    "qty_increments": "",
    "use_config_enable_qty_inc": "",
    "enable_qty_increments": "",
    "is_decimal_divided": "",
    "website_id": "",
    "related_skus": "",
    "related_position": "",
    "crosssell_skus": "",
    "crosssell_position": "",
    "upsell_skus": "",
    "upsell_position": "",
    "additional_images": "",
    "additional_image_labels": "",
    "hide_from_product_page": "",
    "custom_options": "",
    "bundle_price_type": "",
    "bundle_sku_type": "",
    "bundle_price_view": "",
    "bundle_weight_type": "",
    "bundle_values": "",
    "bundle_shipment_type": "",
    "configurable_variations": "",
    "configurable_variation_labels": "",
    "associated_skus": ""
  }
]';
        $sourceDefinition = 'WwogIHsKICAgICJza3UiOiA5MDEzMzUsCiAgICAic3RvcmVfdmlld19jb2RlIjogIiIsCiAgICAiYXR0cmlidXRlX3NldF9jb2RlIjogIkxpZGVycGFwZWwiLAogICAgInByb2R1Y3RfdHlwZSI6ICJzaW1wbGUiLAogICAgImNhdGVnb3JpZXMiOiAiIiwKICAgICJwcm9kdWN0X3dlYnNpdGVzIjogImJhc2UiLAogICAgIm5hbWUiOiAiTG90ZSBwYXJrZXIgbmF2aWRhZCAyMDE5IGNvbnRlbmlkbyA3IGJvbGlncmFmb3MgaW0gLyB1cmJhbiBzdXJ0aWRvcyArIDIgYm9saWdyYWZvcyBpbSBjb3JlIG9ic2VxdWlvIiwKICAgICJkZXNjcmlwdGlvbiI6ICIiLAogICAgInNob3J0X2Rlc2NyaXB0aW9uIjogIkxvdGUgcGFya2VyIG5hdmlkYWQgMjAxOSBjb250ZW5pZG8gNyBib2xpZ3JhZm9zIGltIC8gdXJiYW4gc3VydGlkb3MgKyAyIGJvbGlncmFmb3MgaW0gY29yZSBvYnNlcXVpbyIsCiAgICAid2VpZ2h0IjogNSwKICAgICJwcm9kdWN0X29ubGluZSI6IDEsCiAgICAidGF4X2NsYXNzX25hbWUiOiAiVGF4YWJsZSBHb29kcyIsCiAgICAidmlzaWJpbGl0eSI6ICJDYXRhbG9nLCBTZWFyY2giLAogICAgInByaWNlIjogMTAsCiAgICAic3BlY2lhbF9wcmljZSI6IDUsCiAgICAic3BlY2lhbF9wcmljZV9mcm9tX2RhdGUiOiAiMTAvMjcvMTkiLAogICAgInNwZWNpYWxfcHJpY2VfdG9fZGF0ZSI6ICIxMC8zMS8xOSIsCiAgICAidXJsX2tleSI6IDkwMTMzNSwKICAgICJtZXRhX3RpdGxlIjogIiIsCiAgICAibWV0YV9rZXl3b3JkcyI6ICIiLAogICAgIm1ldGFfZGVzY3JpcHRpb24iOiAiIiwKICAgICJiYXNlX2ltYWdlIjogIiIsCiAgICAiYmFzZV9pbWFnZV9sYWJlbCI6ICIiLAogICAgInNtYWxsX2ltYWdlIjogIiIsCiAgICAic21hbGxfaW1hZ2VfbGFiZWwiOiAiIiwKICAgICJ0aHVtYm5haWxfaW1hZ2UiOiAiIiwKICAgICJ0aHVtYm5haWxfaW1hZ2VfbGFiZWwiOiAiIiwKICAgICJzd2F0Y2hfaW1hZ2UiOiAiIiwKICAgICJzd2F0Y2hfaW1hZ2VfbGFiZWwiOiAiIiwKICAgICJjcmVhdGVkX2F0IjogIjEwLzExLzE5LCA0OjI4IEFNIiwKICAgICJ1cGRhdGVkX2F0IjogIjEwLzI3LzE5LCA4OjQ1IEFNIiwKICAgICJuZXdfZnJvbV9kYXRlIjogIiIsCiAgICAibmV3X3RvX2RhdGUiOiAiIiwKICAgICJkaXNwbGF5X3Byb2R1Y3Rfb3B0aW9uc19pbiI6ICJCbG9jayBhZnRlciBJbmZvIENvbHVtbiIsCiAgICAibWFwX3ByaWNlIjogIiIsCiAgICAibXNycF9wcmljZSI6ICIiLAogICAgIm1hcF9lbmFibGVkIjogIiIsCiAgICAiZ2lmdF9tZXNzYWdlX2F2YWlsYWJsZSI6ICJObyIsCiAgICAiY3VzdG9tX2Rlc2lnbiI6ICIiLAogICAgImN1c3RvbV9kZXNpZ25fZnJvbSI6ICIiLAogICAgImN1c3RvbV9kZXNpZ25fdG8iOiAiIiwKICAgICJjdXN0b21fbGF5b3V0X3VwZGF0ZSI6ICIiLAogICAgInBhZ2VfbGF5b3V0IjogIiIsCiAgICAicHJvZHVjdF9vcHRpb25zX2NvbnRhaW5lciI6ICIiLAogICAgIm1zcnBfZGlzcGxheV9hY3R1YWxfcHJpY2VfdHlwZSI6ICJVc2UgY29uZmlnIiwKICAgICJjb3VudHJ5X29mX21hbnVmYWN0dXJlIjogIiIsCiAgICAiYWRkaXRpb25hbF9hdHRyaWJ1dGVzIjogImNvc3Q9Mi41MDAwIiwKICAgICJxdHkiOiA1MCwKICAgICJvdXRfb2Zfc3RvY2tfcXR5IjogMCwKICAgICJ1c2VfY29uZmlnX21pbl9xdHkiOiAxLAogICAgImlzX3F0eV9kZWNpbWFsIjogMCwKICAgICJhbGxvd19iYWNrb3JkZXJzIjogMCwKICAgICJ1c2VfY29uZmlnX2JhY2tvcmRlcnMiOiAxLAogICAgIm1pbl9jYXJ0X3F0eSI6IDEsCiAgICAidXNlX2NvbmZpZ19taW5fc2FsZV9xdHkiOiAxLAogICAgIm1heF9jYXJ0X3F0eSI6IDEwMDAwLAogICAgInVzZV9jb25maWdfbWF4X3NhbGVfcXR5IjogMSwKICAgICJpc19pbl9zdG9jayI6IDEsCiAgICAibm90aWZ5X29uX3N0b2NrX2JlbG93IjogMSwKICAgICJ1c2VfY29uZmlnX25vdGlmeV9zdG9ja19xdHkiOiAxLAogICAgIm1hbmFnZV9zdG9jayI6IDEsCiAgICAidXNlX2NvbmZpZ19tYW5hZ2Vfc3RvY2siOiAxLAogICAgInVzZV9jb25maWdfcXR5X2luY3JlbWVudHMiOiAxLAogICAgInF0eV9pbmNyZW1lbnRzIjogMSwKICAgICJ1c2VfY29uZmlnX2VuYWJsZV9xdHlfaW5jIjogMSwKICAgICJlbmFibGVfcXR5X2luY3JlbWVudHMiOiAwLAogICAgImlzX2RlY2ltYWxfZGl2aWRlZCI6IDAsCiAgICAid2Vic2l0ZV9pZCI6IDAsCiAgICAicmVsYXRlZF9za3VzIjogIiIsCiAgICAicmVsYXRlZF9wb3NpdGlvbiI6ICIiLAogICAgImNyb3Nzc2VsbF9za3VzIjogIiIsCiAgICAiY3Jvc3NzZWxsX3Bvc2l0aW9uIjogIiIsCiAgICAidXBzZWxsX3NrdXMiOiAiIiwKICAgICJ1cHNlbGxfcG9zaXRpb24iOiAiIiwKICAgICJhZGRpdGlvbmFsX2ltYWdlcyI6ICIvOS8wLzkwMTMzNWdfM181LmpwZywvOS8wLzkwMTMzNWdfM18yLmpwZywvOS8wLzkwMTMzNWcuanBnLC85LzAvOTAxMzM1Z18zXzMuanBnLC85LzAvOTAxMzM1Z18zXzQuanBnLC85LzAvOTAxMzM1Z18xLmpwZywvOS8wLzkwMTMzNWdfNC5qcGciLAogICAgImFkZGl0aW9uYWxfaW1hZ2VfbGFiZWxzIjogIiwsLCwsLCIsCiAgICAiaGlkZV9mcm9tX3Byb2R1Y3RfcGFnZSI6ICIiLAogICAgImN1c3RvbV9vcHRpb25zIjogIiIsCiAgICAiYnVuZGxlX3ByaWNlX3R5cGUiOiAiIiwKICAgICJidW5kbGVfc2t1X3R5cGUiOiAiIiwKICAgICJidW5kbGVfcHJpY2VfdmlldyI6ICIiLAogICAgImJ1bmRsZV93ZWlnaHRfdHlwZSI6ICIiLAogICAgImJ1bmRsZV92YWx1ZXMiOiAiIiwKICAgICJidW5kbGVfc2hpcG1lbnRfdHlwZSI6ICIiLAogICAgImNvbmZpZ3VyYWJsZV92YXJpYXRpb25zIjogIiIsCiAgICAiY29uZmlndXJhYmxlX3ZhcmlhdGlvbl9sYWJlbHMiOiAiIiwKICAgICJhc3NvY2lhdGVkX3NrdXMiOiAiIgogIH0sCiAgewogICAgInNrdSI6IDkwMTMzNSwKICAgICJzdG9yZV92aWV3X2NvZGUiOiAiZGVmYXVsdCIsCiAgICAiYXR0cmlidXRlX3NldF9jb2RlIjogIkxpZGVycGFwZWwiLAogICAgInByb2R1Y3RfdHlwZSI6ICJzaW1wbGUiLAogICAgImNhdGVnb3JpZXMiOiAiIiwKICAgICJwcm9kdWN0X3dlYnNpdGVzIjogIiIsCiAgICAibmFtZSI6ICIiLAogICAgImRlc2NyaXB0aW9uIjogIiIsCiAgICAic2hvcnRfZGVzY3JpcHRpb24iOiAiIiwKICAgICJ3ZWlnaHQiOiAiIiwKICAgICJwcm9kdWN0X29ubGluZSI6ICIiLAogICAgInRheF9jbGFzc19uYW1lIjogIiIsCiAgICAidmlzaWJpbGl0eSI6ICIiLAogICAgInByaWNlIjogIiIsCiAgICAic3BlY2lhbF9wcmljZSI6ICIiLAogICAgInNwZWNpYWxfcHJpY2VfZnJvbV9kYXRlIjogIiIsCiAgICAic3BlY2lhbF9wcmljZV90b19kYXRlIjogIiIsCiAgICAidXJsX2tleSI6ICIiLAogICAgIm1ldGFfdGl0bGUiOiAiIiwKICAgICJtZXRhX2tleXdvcmRzIjogIiIsCiAgICAibWV0YV9kZXNjcmlwdGlvbiI6ICIiLAogICAgImJhc2VfaW1hZ2UiOiAiLzkvMC85MDEzMzVnXzQuanBnIiwKICAgICJiYXNlX2ltYWdlX2xhYmVsIjogIiIsCiAgICAic21hbGxfaW1hZ2UiOiAiLzkvMC85MDEzMzVnXzQuanBnIiwKICAgICJzbWFsbF9pbWFnZV9sYWJlbCI6ICIiLAogICAgInRodW1ibmFpbF9pbWFnZSI6ICIvOS8wLzkwMTMzNWdfNC5qcGciLAogICAgInRodW1ibmFpbF9pbWFnZV9sYWJlbCI6ICIiLAogICAgInN3YXRjaF9pbWFnZSI6ICJub19zZWxlY3Rpb24iLAogICAgInN3YXRjaF9pbWFnZV9sYWJlbCI6ICIiLAogICAgImNyZWF0ZWRfYXQiOiAiIiwKICAgICJ1cGRhdGVkX2F0IjogIiIsCiAgICAibmV3X2Zyb21fZGF0ZSI6ICIiLAogICAgIm5ld190b19kYXRlIjogIiIsCiAgICAiZGlzcGxheV9wcm9kdWN0X29wdGlvbnNfaW4iOiAiIiwKICAgICJtYXBfcHJpY2UiOiAiIiwKICAgICJtc3JwX3ByaWNlIjogIiIsCiAgICAibWFwX2VuYWJsZWQiOiAiIiwKICAgICJnaWZ0X21lc3NhZ2VfYXZhaWxhYmxlIjogIiIsCiAgICAiY3VzdG9tX2Rlc2lnbiI6ICIiLAogICAgImN1c3RvbV9kZXNpZ25fZnJvbSI6ICIiLAogICAgImN1c3RvbV9kZXNpZ25fdG8iOiAiIiwKICAgICJjdXN0b21fbGF5b3V0X3VwZGF0ZSI6ICIiLAogICAgInBhZ2VfbGF5b3V0IjogIiIsCiAgICAicHJvZHVjdF9vcHRpb25zX2NvbnRhaW5lciI6ICIiLAogICAgIm1zcnBfZGlzcGxheV9hY3R1YWxfcHJpY2VfdHlwZSI6ICIiLAogICAgImNvdW50cnlfb2ZfbWFudWZhY3R1cmUiOiAiIiwKICAgICJhZGRpdGlvbmFsX2F0dHJpYnV0ZXMiOiAiIiwKICAgICJxdHkiOiAiIiwKICAgICJvdXRfb2Zfc3RvY2tfcXR5IjogIiIsCiAgICAidXNlX2NvbmZpZ19taW5fcXR5IjogIiIsCiAgICAiaXNfcXR5X2RlY2ltYWwiOiAiIiwKICAgICJhbGxvd19iYWNrb3JkZXJzIjogIiIsCiAgICAidXNlX2NvbmZpZ19iYWNrb3JkZXJzIjogIiIsCiAgICAibWluX2NhcnRfcXR5IjogIiIsCiAgICAidXNlX2NvbmZpZ19taW5fc2FsZV9xdHkiOiAiIiwKICAgICJtYXhfY2FydF9xdHkiOiAiIiwKICAgICJ1c2VfY29uZmlnX21heF9zYWxlX3F0eSI6ICIiLAogICAgImlzX2luX3N0b2NrIjogIiIsCiAgICAibm90aWZ5X29uX3N0b2NrX2JlbG93IjogIiIsCiAgICAidXNlX2NvbmZpZ19ub3RpZnlfc3RvY2tfcXR5IjogIiIsCiAgICAibWFuYWdlX3N0b2NrIjogIiIsCiAgICAidXNlX2NvbmZpZ19tYW5hZ2Vfc3RvY2siOiAiIiwKICAgICJ1c2VfY29uZmlnX3F0eV9pbmNyZW1lbnRzIjogIiIsCiAgICAicXR5X2luY3JlbWVudHMiOiAiIiwKICAgICJ1c2VfY29uZmlnX2VuYWJsZV9xdHlfaW5jIjogIiIsCiAgICAiZW5hYmxlX3F0eV9pbmNyZW1lbnRzIjogIiIsCiAgICAiaXNfZGVjaW1hbF9kaXZpZGVkIjogIiIsCiAgICAid2Vic2l0ZV9pZCI6ICIiLAogICAgInJlbGF0ZWRfc2t1cyI6ICIiLAogICAgInJlbGF0ZWRfcG9zaXRpb24iOiAiIiwKICAgICJjcm9zc3NlbGxfc2t1cyI6ICIiLAogICAgImNyb3Nzc2VsbF9wb3NpdGlvbiI6ICIiLAogICAgInVwc2VsbF9za3VzIjogIiIsCiAgICAidXBzZWxsX3Bvc2l0aW9uIjogIiIsCiAgICAiYWRkaXRpb25hbF9pbWFnZXMiOiAiIiwKICAgICJhZGRpdGlvbmFsX2ltYWdlX2xhYmVscyI6ICIiLAogICAgImhpZGVfZnJvbV9wcm9kdWN0X3BhZ2UiOiAiIiwKICAgICJjdXN0b21fb3B0aW9ucyI6ICIiLAogICAgImJ1bmRsZV9wcmljZV90eXBlIjogIiIsCiAgICAiYnVuZGxlX3NrdV90eXBlIjogIiIsCiAgICAiYnVuZGxlX3ByaWNlX3ZpZXciOiAiIiwKICAgICJidW5kbGVfd2VpZ2h0X3R5cGUiOiAiIiwKICAgICJidW5kbGVfdmFsdWVzIjogIiIsCiAgICAiYnVuZGxlX3NoaXBtZW50X3R5cGUiOiAiIiwKICAgICJjb25maWd1cmFibGVfdmFyaWF0aW9ucyI6ICIiLAogICAgImNvbmZpZ3VyYWJsZV92YXJpYXRpb25fbGFiZWxzIjogIiIsCiAgICAiYXNzb2NpYXRlZF9za3VzIjogIiIKICB9Cl0=';
        $source = $this->om->create(AsyncSource::class, ['sourceType' => 'json', 'sourceDefinition' => $sourceDefinition, 'sourceDataFormat' => 'CSV']);
        $importer = $this->om->create(AsyncImporter::class, ['importType' => 'catalog_product', 'importBehaviour' => 'add', 'uuid' => 'uuid_string']);
        $csvFormat = $this->om->create(CsvFormat::class, ['escape' => CsvFormat::DEFAULT_ESCAPE, 'enclosure' => CsvFormat::DEFAULT_ENCLOSURE, 'delimiter' => ';', 'multipleValueSeparator' => CsvFormat::DEFAULT_MULTIPLE_VALUE_SEPARATOR]);
        $this->importer->execute($source, $importer, $csvFormat);
    }
}
