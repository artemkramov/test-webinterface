<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<? $this->load->view('header'); ?>
    <div class="container">

        <!-- SUMMARY RESULTS -->
        <div class="row">
            <div class="col-sm-12">
                <h3>Validation result:</h3>
                <? if (isset($schema)): ?>
                    <div class="panel-title">
                        <label>Errors: <?= $aggregate['errors'] ?></label>
                    </div>
                    <div class="panel-title">
                        <label>Warnings: <?= $aggregate['warnings'] ?></label>
                    </div>
                <? endif; ?>
            </div>
        </div>
        <!-- END SUMMARY RESULTS -->

        <!-- BOOTSTRAP QUERIES -->
        <div class="row">
            <div class="col-sm-12">
                <h3>Bootstrap queries</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>URL</th>
                        <th>Result</th>
                        <th>Is necessary</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach ($history as $historyData): ?>
                        <?
                        /**
                         * @var ResponseTest $historyData
                         */
                        $isErrorRow = $historyData->result != 'success' && $historyData->isNecessary ? true : false;
                        ?>
                        <tr class="<?= $isErrorRow ? 'error-row' : '' ?>">
                            <td>
                                <span><?= $historyData->uri ?></span>
                            </td>
                            <td><?= $historyData->result ?></td>
                            <td><? if ($historyData->isNecessary): ?>
                                    <span class="glyphicon glyphicon-ok"></span>
                                <? endif; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END BOOTSTRAP QUERIES -->

        <!-- SCHEMA DATA -->
        <? if (isset($schema) && is_array($schema)): ?>
            <div class="schema-wrapper">
                <!-- LEGEND -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border">Legend</legend>
                                <div class="control-group">
                                    Left column contains fields from schema (/cgi/tbl), right column - fields from data
                                    (/cgi/tbl/{TableName}). All incorrect fields marked with <b style="color: red">red
                                        bold font</b>. By default just all tables which contain error are shown. They are marked with red color. To view
                                    all tables click on button "Show all tables".
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <!-- END LEGEND -->

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <button class="btn btn-primary btn-show-all">Show all tables</button>
                            <button class="btn btn-primary btn-hide-all">Hide valid tables</button>
                        </div>
                    </div>
                </div>

                <? foreach ($schema as $schemaData): ?>
                    <? $errors = $schemaErrors[$schemaData->id]; ?>
                    <? $isValid = empty($errors['errors']) && empty($errors['warnings']) && empty($errors['wrongFields']['schema']) && empty($errors['wrongFields']['data']) ?>
                    <div class="row <?= $isValid ? 'schema-valid' : '' ?>">
                        <div class="col-sm-12">
                            <div class="panel panel-<?= $isValid ? 'success' : 'danger' ?>">

                                <!-- SCHEMA TITLE -->
                                <div class="panel-heading panel-collapse" data-toggle="collapse"
                                     data-target="#<?= $schemaData->id ?>">
                                    <h3 class="panel-title panel-title-schema"><?= $schemaData->id ?></h3>
                                    <span class="glyphicon glyphicon-<?= $isValid ? 'ok' : 'remove' ?>"></span>
                                </div>
                                <!-- END SCHEMA TITLE -->

                                <!-- SCHEMA BODY -->
                                <div class="collapse" id="<?= $schemaData->id ?>">
                                    <div class="panel-body">

                                        <!-- ERRORS FOR TABLE -->
                                        <? if (!empty($errors['errors'])): ?>
                                            <div class="alert alert-danger">
                                                <ul>
                                                    <? foreach ($errors['errors'] as $error): ?>
                                                        <li><?= $error ?></li>
                                                    <? endforeach; ?>
                                                </ul>
                                            </div>
                                        <? endif; ?>
                                        <!-- END ERRORS FOR TABLE -->

                                        <!-- WARNINGS FOR TABLE -->
                                        <? if (!empty($errors['warnings'])): ?>
                                            <div class="alert alert-warning">
                                                <ul>
                                                    <? foreach ($errors['warnings'] as $warning): ?>
                                                        <li><?= $warning ?></li>
                                                    <? endforeach; ?>
                                                </ul>
                                            </div>
                                        <? endif; ?>
                                        <!-- WARNINGS FOR TABLE -->

                                        <!-- TEXT ATTRIBUTES OF TABLE -->
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <ul>
                                                    <? foreach (get_object_vars($schemaData) as $key => $value): ?>
                                                        <? if (!is_array($value)): ?>
                                                            <li><?= $key ?>: <?= $value ?></li>
                                                        <? endif; ?>
                                                    <? endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- END TEXT ATTRIBUTES OF TABLE -->

                                        <!-- FIELDS COMPARISION -->
                                        <label>Fields</label>
                                        <div class="row">

                                            <!-- SCHEMA FIELDS -->
                                            <div class="col-sm-6">
                                                <ul class="field-list">
                                                    <? foreach ($schemaData->elems as $field): ?>
                                                        <? if (isset($field->name)): ?>
                                                            <li>
                                                                <? $className = in_array($field->name, $errors['wrongFields']['schema']) ? 'error' : '' ?>
                                                                <span
                                                                    class="<?= $className ?>"><?= $field->name ?></span>
                                                                <ol>
                                                                    <? foreach (get_object_vars($field) as $key => $value): ?>
                                                                        <? if ($key !== 'name'): ?>
                                                                            <li><?= $key ?>: <?= $value ?></li>
                                                                        <? endif; ?>
                                                                    <? endforeach; ?>
                                                                </ol>
                                                            </li>
                                                        <? endif; ?>
                                                    <? endforeach; ?>
                                                </ul>
                                            </div>
                                            <!-- END SCHEMA FIELDS -->

                                            <!-- DATA FIELDS -->
                                            <div class="col-sm-6">
                                                <ul class="field-list">
                                                    <? if (array_key_exists($schemaData->id, $data) && is_array($data[$schemaData->id])): ?>
                                                        <? foreach ($data[$schemaData->id] as $fieldName): ?>
                                                            <? if (isset($fieldName)): ?>
                                                                <li>
                                                                    <? $className = in_array($fieldName, $errors['wrongFields']['data']) ? 'error' : '' ?>
                                                                    <span
                                                                        class="<?= $className ?>"><?= $fieldName ?></span>
                                                                </li>
                                                            <? endif; ?>
                                                        <? endforeach; ?>
                                                    <? else: ?>
                                                        <label>Table data not found</label>
                                                    <? endif; ?>
                                                </ul>
                                            </div>
                                            <!-- END DATA FIELDS -->

                                        </div>
                                        <!-- END FIELDS COMPARISION -->

                                    </div>
                                </div>
                                <!-- END SCHEMA BODY -->

                            </div>


                        </div>
                    </div>

                <? endforeach; ?>
            </div>
        <? else: ?>
            <div class="alert alert-danger">
                Cannot parse schema data (/cgi/tbl). Check if JSON string is valid.
            </div>
        <? endif; ?>
        <!-- END SCHEMA DATA -->
    </div>
<? $this->load->view('footer'); ?>