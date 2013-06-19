<?php
/**
 * FormTest
 */
namespace Sledgehammer;
/**
 * Unittest for the Form class
 * @package MVC
 */
class FormTest extends TestCase {

	function test_render() {
		$form = new Form(array(
			'fields' => array(
				new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\">\n<fieldset>\n\t<input name=\"field1\" type=\"text\" />\n</fieldset>\n</form>", view_to_string($form));
	}

	function test_render_with_labels() {
		$form = new Form(array(
			'fieldset' => false,
			'fields' => array(
				'Label1' => new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\">\n\t<label>Label1</label><input name=\"field1\" type=\"text\" />\n</form>", view_to_string($form));
	}

	function test_render_with_control_groups() {
		$form = new Form(array(
			'class' => 'form-horizontal',
			'fieldset' => false,
			'fields' => array(
				'Label1' => new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\" class=\"form-horizontal\">\n\t<div class=\"control-group\"><label class=\"control-label\">Label1</label><div class=\"controls\"><input name=\"field1\" type=\"text\" /></div></div>\n</form>", view_to_string($form));
	}

	function test_import() {
		$form = new Form(array(
			'action' => '/',
			'fields' => array(
				new Input(array('name' => 'field1'))
			)
		));
		$data = $form->import($error, array('field1' => 'value1'));
		$this->assertEquals(array('field1' => 'value1'), $data);
	}

}

?>