<!-- The file upload form used as target for the file upload widget -->
<?php if ($this->showForm) echo CHtml::beginForm($this->url, 'post', $this->htmlOptions); ?>
<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
<div class="row fileupload-buttonbar">
    <div class="col-lg-7 col-md-7">
        <!-- The fileinput-button span is used to style the file input field as button -->
        <span class="btn btn-success fileinput-button">
            <i class="glyphicon glyphicon-plus"></i>
            <span><?= $this->t('1#Add files|0#Choose file', $this->multiple); ?></span>
            <?php
            if ($this->hasModel()) :
                echo CHtml::activeFileField($this->model, $this->attribute, $htmlOptions) . "\n";
            else :
                echo CHtml::fileField($name, $this->value, $htmlOptions) . "\n";
            endif;
            ?>
        </span>
        <?php if ($this->multiple): ?>
        <button type="submit" class="btn btn-primary start">
            <i class="glyphicon glyphicon-upload"></i>
            <span>Start upload</span>
        </button>
        <button type="reset" class="btn btn-warning cancel">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span>Cancel upload</span>
        </button>
        <button type="button" class="btn btn-danger delete">
            <i class="glyphicon glyphicon-trash"></i>
            <span>Delete</span>
        </button>
        <input type="checkbox" class="toggle">
        <?php endif; ?>
        <!-- The global file processing state -->
        <span class="fileupload-process"></span>
    </div>
    <!-- The global progress state -->
    <div class="col-lg-5 col-md-5 fileupload-progress fade">
        <!-- The global progress bar -->
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
        </div>
        <!-- The extended global progress state -->
        <div class="progress-extended">&nbsp;</div>
    </div>
</div>
<!-- The table listing the files available for upload/download -->
<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
</form>
<?php if ($this->showForm) echo CHtml::endForm(); ?>
