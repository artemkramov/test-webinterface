<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Welcome
 * Main controller which contains all necessary actions
 */
class Welcome extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        /**
         * Load possible versions of device
         */
        $countries = $this->config->item('countries');
        $this->load->view('welcome_message', [
            'countries' => $countries
        ]);
    }

    /**
     * Connect action
     * where user tries to connect to device
     * by the given address and credentials
     */
    public function connect()
    {
        $data = $this->input->post();
        $this->load->library('HttpClient', $data);
        $response = $this->httpclient->connect();
        if (!empty($response['errorMessage'])) {
            $this->session->set_flashdata('errorMessage', $response['errorMessage']);
            redirect('/welcome/index');
        }
        redirect('/welcome/build');
    }

    /**
     * Build the report about device
     */
    public function build()
    {
        /**
         * Load credentials from session
         */
        $data = $this->session->userdata('credentials');
        if (empty($data)) {
            redirect('/welcome/index');
        }
        $this->load->library('HttpClient', $data);
        $data = $this->httpclient->buildData();
        $this->load->view('build', $data);
    }

    /**
     * Logout action
     * Clears the session data and redirects to the main page
     */
    public function logout()
    {
        $this->session->unset_userdata('credentials');
        redirect('/welcome/index');
    }

}
