<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Mutasim Ridlo, S.Kom
 * @copyright Copyright (c) 2014-2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://codeigniter.com
 */
defined('BASEPATH') or exit('No direct script access allowed');

$lang['form_validation_required'] = '{field} tidak boleh kosong.';
$lang['form_validation_isset'] = '{field} harus memiliki nilai.';
$lang['form_validation_valid_email'] = '{field} harus berisi alamat email yang sah.';
$lang['form_validation_valid_emails'] = '{field} harus berisi semua alamat email yang sah.';
$lang['form_validation_valid_url'] = '{field} harus berisi URL yang sah.';
$lang['form_validation_valid_ip'] = '{field} harus berisi IP yang sah.';
$lang['form_validation_min_length'] = '{field} harus setidaknya {param} panjang karakter.';
$lang['form_validation_max_length'] = '{field} tidak dapat melebihi {param} panjang karakter.';
$lang['form_validation_exact_length'] = '{field} harus tepat {param} panjang karakter.';
$lang['form_validation_alpha'] = '{field} hanya dapat berisi karakter abjad.';
$lang['form_validation_alpha_numeric'] = '{field} hanya dapat berisi karakter alpha-numerik.';
$lang['form_validation_alpha_numeric_spaces'] = '{field} hanya dapat berisi karakter alpha-numerik dan spasi.';
$lang['form_validation_alpha_dash'] = '{field} hanya dapat berisi karakter alpha-numeric, garis bawah, dan tanda hubung.';
$lang['form_validation_numeric'] = '{field} harus hanya berisi angka.';
$lang['form_validation_is_numeric'] = '{field} harus berisi karakter numerik.';
$lang['form_validation_integer'] = '{field} harus berisi integer.';
$lang['form_validation_regex_match'] = '{field} tidak dalam format yang benar.';
$lang['form_validation_matches'] = '{field} tidak cocok dengan bidang {param}.';
$lang['form_validation_differs'] = '{field} harus berbeda dari bidang {param}.';
$lang['form_validation_is_unique'] = '{field} harus berisi nilai unik.';
$lang['form_validation_is_natural'] = '{field} harus hanya mengandung angka.';
$lang['form_validation_is_natural_no_zero'] = '{field} harus hanya berisi angka dan harus lebih besar dari nol.';
$lang['form_validation_decimal'] = '{field} harus berisi angka desimal.';
$lang['form_validation_less_than'] = '{field} harus berisi angka kurang dari {param}.';
$lang['form_validation_less_than_equal_to'] = '{field} harus berisi angka kurang dari atau sama dengan {param}.';
$lang['form_validation_greater_than'] = '{field} harus berisi angka lebih besar dari {param}.';
$lang['form_validation_greater_than_equal_to'] = '{field} harus berisi angka yang lebih besar dari atau sama dengan {param}.';
$lang['form_validation_error_message_not_set'] = 'Tidak dapat mengakses pesan kesalahan sesuai dengan nama bidang Anda {field}.';
$lang['form_validation_in_list'] = '{field} harus menjadi salah satu dari: {param}.';

// ! Custome Form Validation

$lang['form_validation_file']							= '{field} tidak boleh kosong.';
$lang['form_validation_max_size']						= "File {field} terlalu besar (max {param}).";
$lang['form_validation_min_size']						= "File {field} terlalu kecil (min {param}).";
$lang['form_validation_mimes']				= "File {field} dianjurkan {param}.";
$lang['form_validation_except_mimes']			= "File {field} tidak dianjurkan {param}.";
$lang['form_validation_max_dim']				= "Dimensi file {field} terlalu besar.";
$lang['form_validation_min_dim']				= "Dimensi file {field} terlalu kecil.";
$lang['form_validation_exact_dim']			= "Dimensi file {field} tidak memenuhi kriteria.";
$lang['form_validation_valid_hour']						= "{field} harus jam yang valid.";
$lang['form_validation_error_max_filesize_phpini']		= "File melebihi direktif upload_max_filesize dari php.ini.";
$lang['form_validation_error_max_filesize_form']		= "File yang diunggah melebihi direktif MAX_FILE_SIZE yang ditentukan dalam formulir HTML.";
$lang['form_validation_error_partial_upload']			= "File itu hanya diunggah sebagian.";
$lang['form_validation_error_temp_dir']					= "Kesalahan direktori.";
$lang['form_validation_error_disk_write']				= "Kesalahan penulisan disk.";
$lang['form_validation_error_stopped']					= "Pengunggahan file dihentikan oleh ekstensi";
$lang['form_validation_error_unexpected']				= "Kesalahan pengunggahan file tak terduga. Kesalahan: {field}";
$lang['form_validation_valid_date']						= "{field} harus tanggal yang valid.";
$lang['form_validation_valid_range_date']				= "{field} harus berupa rentang tanggal yang valid.";
