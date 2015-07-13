<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Slider Module
 *
 * @package		PyroCMS
 * @subpackage	Slider Module
 * @author		Chris Harvey
 * @link 		http://www.chrisnharvey.com/
 *
 */
class Admin extends Admin_Controller
{
	public $section = 'sliders';

	public $modulename = "slider";
	public $current_namespace = "sliders";
	public $current_streamname = "sliders";

	public function __construct()
	{
		parent::__construct();

		// Load the streams driver
		$this->load->driver('Streams');

		// Load the language file
		$this->lang->load('slider');

		// Load the cache
		$this->load->driver('cache', array('adapter' => 'file'));
	}

	public function index()
	{
		// Display a list of articles
		/*$params = array(
			'stream'    => $this->current_namespace,
			'namespace' => $this->current_streamname,
			'order_by'  => 'ordering_count',
			'sort'      => 'asc'
		);*/

		//$data['entries'] = $this->streams->entries->get_entries($params);

		/*$this->template->append_css('module::sortable.css')
					   ->append_js('module::sortable.js')
					   ->build('admin/sliders_entries', $data);*/

		$extra = array(
			'return'			=> 'admin/slider',
			'success_message'	=> lang('slider:create:success'),
			'failure_message'	=> lang('slider:create:fail'),
			'title'				=> lang('slider:sections:sliders:title'),
			'columns' 			=> array('id', 'slider_status', 'slider_slug','slider_language'),
			'buttons'			=> array(array('label' => lang('global:edit'),'url' => 'admin/slider/edit/-entry_id-'),
										array('label' => lang('global:delete'),'url' => 'admin/slider/delete/-entry_id-', 'confirm'=>true),
										array('label' => lang('slider:buttons:add_image'),'url' => 'admin/slider/slides/create/-entry_id-'),
										array('label' => lang('slider:buttons:edit_image'),'url' => 'admin/slider/slides/index/-entry_id-'),
										array('label' => lang('slider:buttons:duplicate'),'url' => 'admin/slider/duplicate/-entry_id-'))
		);

		$this->streams->cp->entries_table($this->current_streamname, $this->current_namespace, 20, "page/", true, $extra);
	}

	public function create()
	{
		/*
		$folder = $this->get_folder_byname("sliders");

		//Check if it already exist..
		if (!$folder["status"])
		{
			// Create a folder to store the slider images
			$folder = Files::create_folder(0, 'sliders');
		}
		*/
		if ($this->input->post()) $this->cache->delete(md5(BASE_URL . $this->modulename));

		$extra = array(
			'return'			=> 'admin/slider',
			'success_message'	=> lang('slider:create:success'),
			'failure_message'	=> lang('slider:create:fail'),
			'title'				=> lang('slider:create:title')
		);

		$this->streams->cp->entry_form($this->current_streamname, $this->current_namespace, 'new', NULL, TRUE, $extra);
	}

	public function edit($id)
	{
		if ($this->input->post()) $this->cache->delete(md5(BASE_URL . $this->modulename));

		$extra = array(
 			'return' => 'admin/slider'
 		);

 		$this->streams->cp->entry_form($this->current_streamname, $this->current_namespace, 'edit', $id, TRUE, $extra);
	}

	public function duplicate($id)
	{

		//if ($this->input->post()) $this->cache->delete(md5(BASE_URL . $this->modulename));

		//get the current slider by $id
		$base_slider = (array) $this->streams->entries->get_entry($id, $this->current_streamname, $this->current_namespace, false);

		//insert_entry new sliders skipping id to be different, indeed.
		$new_slider_id = $this->streams->entries->insert_entry($base_slider, $this->current_streamname, $this->current_namespace, array('id'));

		//get the associated slides
		$params = array(
			'stream'    => "slides",
			'namespace' => "slides",
			'order_by'  => 'ordering_count',
			'sort'      => 'asc',
			'where'		=> 'slider_id = '.$id
		);
		$base_slider_slides = $this->streams->entries->get_entries($params)["entries"];

		$new_slide = array();
		//insert_entry slides to this slider.
		foreach ($base_slider_slides as $base_slide)
		{
			$new_slide = array(
					"slider_id" => $new_slider_id,
					"slide_title" => $base_slide["slide_title"],
					"slide_desc"=> $base_slide["slide_desc"],
					"slide_image"=>$base_slide["slide_image"]["id"],
					"slide_link"=>$base_slide["slide_link"]);
			/**/
			$this->streams->entries->insert_entry($new_slide, "slides", "slides");
		}

		redirect('admin/slider');
	}

	public function live($id)
	{
		$id = (int)$id;

		$update = $this->db->update($this->current_namespace, array('status' => 'live'), array('id' => $id));

 		if ($update)
 		{
 			$this->cache->delete(md5(BASE_URL . $this->modulename));
 			$this->session->set_flashdata('success', 'Image successfully set to live');
 		}
 		else
 		{
 			$this->session->set_flashdata('error', 'Unable to set the image to live');
 		}

 		redirect('admin/slider/index');
	}

	public function draft($id)
	{
		$id = (int)$id;

		$update = $this->db->update($this->current_streamname, array('status' => 'draft'), array('id' => $id));

 		if ($update)
 		{
 			$this->cache->delete(md5(BASE_URL . $this->modulename));
 			$this->session->set_flashdata('success', 'Image successfully set to draft');
 		}
 		else
 		{
 			$this->session->set_flashdata('error', 'Unable to set the image to draft');
 		}

 		redirect('admin/slider/index');
	}

	public function delete($id)
	{
		$id = (int)$id;

 		$delete = $this->db->delete($this->current_streamname, array('id' => $id));

 		if ($delete)
 		{
 			$this->cache->delete(md5(BASE_URL . $this->modulename));
 			$this->session->set_flashdata('success', 'Image deleted successfully');
 		}
 		else
 		{
 			$this->session->set_flashdata('error', 'Unable to delete image');
 		}

 		redirect('admin/slider/index');
	}

	public function fields($action = null, $field = null)
	{
		if ($action == null) {

			$this->streams->cp->assignments_table($this->current_namespace, $this->current_namespace, null, null, true, array(
				'title'   => 'Slider Fields',
				'buttons' => array(
					array(
						'label' => 'Edit',
						'url'   => "admin/slider/fields/edit/-assign_id-"
					),
					array(
						'label'   => 'Delete',
						'url'     => "admin/slider/fields/delete/-assign_id-",
						'confirm' => true
					)
				)
			));

		} elseif (($action == 'edit' and $field) or $action == 'new') {

			$this->streams->cp->field_form($this->current_streamname, $this->current_namespace, $action, 'admin/slider/fields', $field, array(), true, array(
				'title'   => 'Edit Field',
			));

		} elseif ($action == 'delete' and $field) {

			$query = $this->db->select('data_fields.field_slug')
				->join('data_fields', 'data_field_assignments.field_id = data_fields.id')
				->where('data_field_assignments.id', $field)
				->get('data_field_assignments');

			if ( ! $query->num_rows()) show_404();

			$this->streams->fields->delete_field($query->row()->field_slug, $this->current_streamname);

			$this->session->set_flashdata('success', 'Field deleted successfully');

			redirect('admin/slider/fields');

		} else {

			show_404();

		}
	}

	public function reorder()
	{
		if ($this->input->is_ajax_request())
 		{
 			$order = explode(',', $this->input->post('order'));

 			// Start the transaction
 			$this->db->trans_start();

 			$i = 1;
 			foreach ($order as $id)
 			{
 				$this->db->update($this->current_streamname, array('ordering_count' => $i), array('id' => $id));
 				$i++;
 			}

 			// End the transaction
 			$this->db->trans_complete();

 			if ($this->db->trans_status() === FALSE)
 			{
 				set_status_header(500);
 			}
 			else
 			{
 				$this->cache->delete(md5(BASE_URL . $this->current_streamname));
 				set_status_header(200);
 			}
 		}
 		else
 		{
 			show_404();
 		}
	}

	/**
    *  	Copied from Files:search and adapt to get all properties
    *	In: file_folders_m->select()
    */
    public function get_folder_byname($name)
    {
        if (!isset($name)) return FALSE;
        $results = array();
        $this->file_folders_m->select('id, parent_id, slug, name, location, remote_container, date_added, sort');

        $this->file_folders_m->like('name', $name)
                ->or_like('location', $name)
                ->or_like('remote_container', $name);

        $results['folder'] = $this->file_folders_m->get_all();
        
        if ($results['folder'])
        {
                return Files::result(TRUE, NULL, NULL, $results);
        }
        return Files::result(FALSE, lang('files:no_records_found'));
    }
}