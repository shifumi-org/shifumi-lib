<?php defined('ABSPATH') or die('Access denied.'); ?>

<script id="wdt-constructor-column-block-template" type="text/x-jsrender">
    <div class="wdt-constructor-column-block col-sm-3">

        <div class="card m-b-15 m-t-15">

            <div class="card-header col-sm-12 ch-alt p-t-10 p-b-10 p-r-0 p-l-0">

                <div class="col-sm-10">
                    <div class="fg-line">
                        <input type="text" class="form-control input-sm wdt-constructor-column-name" value="{{>name}}">
                        <i class="wpdt-icon-pen"></i>
                    </div>
                </div>

                <ul class="actions wdt-constructor-remove-column">
                    <li>
                        <a>
                            <i class="wpdt-icon-trash-reg"></i>
                        </a>
                    </li>
                </ul>

            </div>

            <div class="card-body card-padding">

                <div class="col-sm-12 p-t-5 p-0">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Type', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <div class="select">
                                <select class="selectpicker wdt-constructor-column-type">
                                    <option value="input"><?php esc_html_e('One line string', 'wpdatatables'); ?></option>
                                    <option value="memo"><?php esc_html_e('Multi-line string', 'wpdatatables'); ?></option>
                                    <option value="select"><?php esc_html_e('One-line selectbox', 'wpdatatables'); ?></option>
                                    <option value="multiselect"><?php esc_html_e('Multi-line selectbox', 'wpdatatables'); ?></option>
                                    <option value="int"><?php esc_html_e('Integer', 'wpdatatables'); ?></option>
                                    <option value="float"><?php esc_html_e('Float', 'wpdatatables'); ?></option>
                                    <option value="date"><?php esc_html_e('Date', 'wpdatatables'); ?></option>
                                    <option value="datetime"><?php esc_html_e('Datetime', 'wpdatatables'); ?></option>
                                    <option value="time"><?php esc_html_e('Time', 'wpdatatables'); ?></option>
                                    <option value="link"><?php esc_html_e('URL Link', 'wpdatatables'); ?></option>
                                    <option value="email"><?php esc_html_e('E-mail', 'wpdatatables'); ?></option>
                                    <option value="image"><?php esc_html_e('Image', 'wpdatatables'); ?></option>
                                    <option value="file"><?php esc_html_e('Attachment', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 wdt-constructor-possible-values-block p-0" style="display: none;">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Possible values', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <input class="form-control input-sm wdt-constructor-possible-values" value=""/>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12  p-0">
                    <h5 class="c-black m-b-10">
                        <?php esc_html_e('Editor predefined value', 'wpdatatables'); ?>
                    </h5>
                    <div class="form-group">
                        <div class="fg-line">
                            <input type="text" class="form-control input-sm wdt-constructor-default-value" value="" placeholder="Enter predifined value">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>


</script>