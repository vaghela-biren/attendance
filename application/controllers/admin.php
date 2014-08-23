<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller {

	public $admin_data = array();

	public function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->data['page'] = 0;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['rows'] = $this->attendance_m->get_distinct();
		$this->load->model('period_m');
		$this->data['period'] = $this->period_m->get();
		$period = $this->input->post('period');
		if($period) {
			$temp = explode('#', $period);
			$array = array('from_date' => $temp[0], 'to_date' => $temp[1]);
			$this->data['rows2'] = $this->attendance_m->get_distinct_select($array);
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/main_layout');
	}

	public function subjects() {
		$this->data['page'] = 3;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['rows'] = array();
		$this->data['semesters'] = $this->subject_m->get_distinct_semester_all();
		$semester = $this->input->post('semester');
		if($semester) {
			$array = array('semester' => $semester);
			$this->data['rows'] = $this->subject_m->get_by($array);
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/subjects_layout');
	}

	public function edit_subject() {
		$this->data['page'] = 3;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['confirmation'] = 0;
		$this->data['subject_code'] = $this->input->post('subject_code');
		if($this->input->post('submit')) {
			$data = array('subject_name' => $this->input->post('subject_name'), 'semester' => $this->input->post('semester'), 'subject_abbr' => $this->input->post('subject_abbr'));
			if($this->subject_m->save($data, $this->data['subject_code'])) {
				$this->data['confirmation'] = 1;
			} else {
				$this->data['confirmation'] = 2;
			}
		}
		$this->data['subject'] = $this->subject_m->get_by(array('subject_code' => $this->data['subject_code']));
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/edit_subject_layout');
	}

	public function students() {
		$this->data['page'] = 2;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['rows'] = array();
		$this->data['semesters'] = $this->student_m->get_distinct_semester();
		if($this->input->get('confirmation')) {
			$this->data['confirmation'] = 3;
		}
		if($this->input->post('increment')) {
			if($this->student_m->update_semester()) {
				$this->data['confirmation'] = 1;
			} else {
				$this->data['confirmation'] = 2;
			}
		}
		$semester = $this->input->post('semester');
		if($semester) {
			$array = array('semester' => $semester);
			$this->data['rows'] = $this->student_m->get_by($array);
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/students_layout');
	}

	public function add_student() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 2;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['rows'] = array();
		$this->load->model('studies_m');
		if($this->input->post('submit'))
		{
			$rules = $this->student_m->rules1;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE)
	    	{
				$array = array('student_id' => $this->input->post('student_id'),'roll_number' =>  $this->input->post('roll_number'),'student_name' => $this->input->post('student_name'),'semester' =>  $this->input->post('semester'));
			    if($this->student_m->add_stu($array)){
			    	if($this->input->post('semester') < 7) {
			    		$this->studies_m->add_student($array);
			    		$this->data['confirmation'] = 1;
			    	} else {
			    		redirect('admin/add_student_final?id=' . $this->input->post('student_id'));
			    	} 
			    } else {
			    	$this->data['confirmation'] = 2;
			    }
			} else {
				$this->data['confirmation'] = 3;
			}
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/add_student_layout');
	}

	public function add_student_final() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 2;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['student_id'] = $this->input->get('id');
		$this->data['student_name'] = $this->student_m->get_name_final(array('student_id' => $this->input->get('id')));
		$semester = $this->student_m->get_semester(array('student_id' => $this->input->get('id')));
		$this->data['subjects'] = $this->subject_m->get_by(array('semester' => $semester));
		$count = $this->subject_m->count_subjects($semester);
		if($this->input->post('submit')) {
			$this->load->model('studies_m');
			for($counter=1;$counter<=$count;$counter++) {
				if($this->input->post($counter)) {
					//echo $this->input->post($counter);
					$this->studies_m->save(array('student_id' => $this->input->get('id'), 'subject_code' => $this->input->post($counter)));
				}
			}
			redirect('admin/students?confirmation=3');
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/add_student_layout_final');
	}	

	public function edit_student() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 2;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['student_id'] = $this->input->post('student_id');
		$this->load->model('student_m');
		$array = array('student_id' => $this->data['student_id']);
		if($this->input->post('submit')) {
			$rules = $this->student_m->rules;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE) {
	    		$array = array('student_name' => $this->input->post('student_name'),'student_id' => $this->data['student_id'],'semester' => $this->input->post('semester'));
				if($this->student_m->save($array,$this->data['student_id'])) {
					$this->data['confirmation'] = 1;
				} else {
					$this->data['confirmation'] = 2;
				}
	    	}
    	}
    	$this->data['student'] = $this->student_m->get_by($array);
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/edit_student_layout');
	}

	public function new_period() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 0;
		$this->data['name'] = $this->session->userdata('name');
		if($this->input->post('submit')) {
			$this->load->model('period_m');
			$rules = $this->period_m->rules;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE) {
				$array = array('from_date' => $this->input->post('from_date'), 'to_date' => $this->input->post('to_date'));
				if($this->period_m->insert($array)) {
					$this->data['confirmation'] = 1;
				} else {
					$this->data['confirmation'] = 2;
				}
	    	} else {
				$this->data['confirmation'] = 3;
			}
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/new_period_layout');
	}

	public function total_attendance() {
		$this->data['page'] = 0;
		$this->data['name'] = $this->session->userdata('name');
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/total_attendance_layout');
	}

	public function total_attendance_options() {
		$this->data['page'] = 0;
		$this->data['name'] = $this->session->userdata('name');
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/total_attendance_options_layout');
	}	

	public function view_attendance() {
		$code = $this->input->post('subject_code');
		$array = array('subject_code' => $code);
		$this->data['subject'] = $this->subject_m->get_by($array, TRUE);
		$this->data['page'] = 0;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['from_date'] = $this->input->post('from_date');
		$this->data['to_date'] = $this->input->post('to_date');
		$this->data['subject_code'] = $code;
		$this->data['rows'] = $this->admin_m->get_view_attendance($this->data);
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/view_attendance_layout');
	}

	public function teachers() {
		$this->data['page'] = 1;
		$this->data['name'] = $this->session->userdata('name');
		$this->load->model('teacher_m');
		$this->data['rows'] = $this->teacher_m->get();
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/teachers_layout');
	}

	public function edit_teacher() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 1;
		$this->data['name'] = $this->session->userdata('name');
		$this->load->model('teacher_m');
		$this->data['teacher_id'] = $this->input->post('teacher_id');
		$array = array('teacher_id' => $this->data['teacher_id']);
		$username = $this->teacher_m->get_username($array);
		if($this->input->post('submit')) {
			$rules = $this->teacher_m->rules3;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE) {
				$array = array('teacher_name' => $this->input->post('teacher_name'), 'username' => $this->input->post('username'));
				if($username != $array['username']) {
					if($this->teacher_m->check_username($array)) {
						$this->data['confirmation'] = 4;
					}
				} else {
					if($this->teacher_m->save($array,$this->data['teacher_id'])) {
						$this->data['confirmation'] = 1;
					} else {
						$this->data['confirmation'] = 2;
					}
				}
	    	} else {
				$this->data['confirmation'] = 3;
			}
		}
		$this->data['teacher'] = $this->teacher_m->get_by($array);
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/edit_teacher_layout');
	}

	public function add_teacher() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 1;
		$this->data['name'] = $this->session->userdata('name');
		if($this->input->post('submit')) {
			$this->load->model('teacher_m');
			$rules = $this->teacher_m->rules2;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE) {
				$array = array('teacher_name' => $this->input->post('teacher_name'), 'username' => $this->input->post('username'), 'password' => $this->teacher_m->hash($this->input->post('password')));
				if($this->teacher_m->check_username($array)) {
					$id = $this->teacher_m->save($array);
					unset($array);
					$array = array('subject_code' => $this->input->post('subject_code'), 'subject_name' => $this->input->post('subject_name'), 'semester' => $this->input->post('semester'), 'teacher_id' => $id);
					if($this->subject_m->insert($array)) {
						$this->data['confirmation'] = 1;
					} else {
						$this->data['confirmation'] = 2;
					}
				} else {
					$this->data['confirmation'] = 4;
				}
	    	} else {
				$this->data['confirmation'] = 3;
			}
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/add_teacher_layout');
	}

	public function view_teacher() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 1;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['teacher_id'] = $this->input->post('teacher_id');
		$array = array('teacher_id' => $this->data['teacher_id']);
		$this->data['teacher1']=$this->teacher_m->get_by($array,TRUE);
		if($this->input->post('submit')){
    		$array1 = array('subject_code' => $this->input->post('subject_code'),'teacher_id' => $this->data['teacher_id']);
    		if($this->subject_m->unlink_code($array1)) {
				$this->data['confirmation'] = 1;
			} else {
				$this->data['confirmation'] = 2;
			}
		}
		$this->data['teacher2']=$this->subject_m->get_by($array);
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/view_teacher_layout');
	}

	public function link_subject() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 1;
		$this->data['name'] = $this->session->userdata('name');
		$this->data['teacher_id'] = $this->input->post('teacher_id');
		$this->data['teacher_name'] = $this->teacher_m->get_name(array('teacher_id' => $this->data['teacher_id']));
    	if($this->input->post('submit')) {
    		$array = array('subject_code' => $this->input->post('subject_code'),'teacher_id' => $this->data['teacher_id']);
			if($this->subject_m->link_code($array)) {
				$this->data['confirmation'] = 1;
			} else {
				$this->data['confirmation'] = 2;
			}
    	}
		$this->data['rows'] = $this->subject_m->get_subject_code_for_teacher($this->data['teacher_id']);
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/link_subject_layout');
	}

public function add_subject() {
		$this->data['confirmation'] = "";
		$this->data['page'] = 3;
		$this->data['name'] = $this->session->userdata('name');
		if($this->input->post('submit'))
		{
			$rules = $this->subject_m->rules2;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE)
	    	{
				$array = array('subject_name' => $this->input->post('subject_name'));
				if($this->subject_m->check_subjectname($array))
				{
					//$id = $this->subject_m->save($array);
					unset($array);
					$array = array('subject_code' => $this->input->post('subject_code'));
					if($this->subject_m->check_subject_code($array)) {
						unset($array);
						$array = array('subject_code' => $this->input->post('subject_code'), 'subject_name' => $this->input->post('subject_name'), 'subject_abbr' => $this->input->post('subject_abbr'), 'semester' => $this->input->post('semester'), 'teacher_id' => 0);
						if($this->subject_m->insert($array))
						{
							$this->data['confirmation'] = 1;
						}
						else
						{
							$this->data['confirmation'] = 2;
						}

					} else {
						$this->data['confirmation'] = 5;
					}
				}
				else
				{
					$this->data['confirmation'] = 4;
				}
	    	}
	    	else
	    	{
				$this->data['confirmation'] = 3;
			}
		}
		$this->load->view('admin/components/admin_header', $this->data);
		$this->load->view('admin/add_subject_layout');
	}

	public function account() {
		$id = $this->session->userdata('id');
		$admin_data = $this->admin_m->get($id);
		$admin_data->site_name = config_item('site_name');
		$admin_data->meta_title = 'Attendance Management System';
		$admin_data->page = -1; // No highlights in the navigation bar
		$admin_data->name = $admin_data->admin_name;
		if($this->input->get('confirmation')) {
			$admin_data->confirmation = 1;
		}
		$this->load->view('admin/components/admin_header', $admin_data);
		$this->load->view('admin/account_layout');
	}

	public function change_password() {
		$id = $this->session->userdata('id');
		$admin_data = $this->admin_m->get($id);
		$admin_data->site_name = config_item('site_name');
		$admin_data->meta_title = 'Attendance Management System';
		$admin_data->page = -1; // No highlights in the navigation bar
		$admin_data->name = $admin_data->admin_name;
		$admin_data->confirmation = "";
		if($this->input->post('submit')) {
			$rules = $this->admin_m->rules1;
	    	$this->form_validation->set_rules($rules);
	    	if ($this->form_validation->run() == TRUE) {
	    		$array = array('password' => $this->admin_m->hash($this->input->post('new_password')));
	    		if($this->admin_m->check_old_password($admin_data->admin_id)) {
					if($this->admin_m->check_new_password()) {
						if($this->admin_m->save($array,$admin_data->admin_id)) {
							$admin_data->confirmation = 1;
							redirect('/admin/account?confirmation=1');
						}
					} else {
						$admin_data->confirmation = 3;
					}
	    		} else {
	    			$admin_data->confirmation = 2;
	    		}
	    	} else {
	    		$admin_data->confirmation = 4;
	    	}
		}
		$this->load->view('admin/components/admin_header', $admin_data);
		$this->load->view('admin/change_password_layout');
	}

	public function login() {
		$this->admin_m->loggedin() == FALSE || redirect('admin/');
		$rules = $this->admin_m->rules;
    	$this->form_validation->set_rules($rules);
    	if ($this->form_validation->run() == TRUE) {
    		if($this->admin_m->login() == TRUE) {
    			redirect('admin/');
    		} else {
    			$this->session->set_flashdata('error', 'That email/password combination does not exist');
    			redirect('admin/login', 'refresh');
    		}
    	}
		$this->load->view('bootstrap/header_login', $this->data);
		$this->load->view('admin/login');
		$this->load->view('bootstrap/footer_login');
	}

	public function logout() {
		$this->admin_m->logout();
		redirect('welcome');
	}

	public function show() {
		$this->load->model('teacher_m');
		$students = $this->student_m->get();
		$teachers = $this->teacher_m->get(2);
		var_dump($students);
		var_dump($teachers);
	}

	public function save() {
		$data = array(
			'admin_name' => 'Admin'
		);
		$admins = $this->admin_m->save($data, 1); // will update instead of insert because of the second argument
		var_dump($admins);
	}

	public function delete() {
    	$this->teacher_m->delete(3); // deletes an entry in the teacher table with the id 3
    }

}

?>
