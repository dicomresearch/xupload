<?php /** @var $this XUpload */ ?>
<!-- The file upload form used as target for the file upload widget -->
<?php if ($this->showForm) echo CHtml::beginForm($this->url, 'post', $this->htmlOptions); ?>
<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
<div class="row fileupload-buttonbar">
    <div class="col-lg-6 col-md-6">
        <!-- The fileinput-button span is used to style the file input field as button -->
        <?php if (!$this->checkAddOperationName || Yii::app()->getUser()->checkAccess($this->checkAddOperationName)): ?>
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
        <?php endif; ?>
        <?php if ($this->multiple): ?>
            <?php if (!$this->autoUpload) :?>
            <button type="submit" class="btn btn-primary start">
            <i class="glyphicon glyphicon-upload"></i>
            <span><?= $this->t('Start upload') ?></span>
            <?php endif;?>
        </button>
        <?php if (!$this->checkAddOperationName || Yii::app()->getUser()->checkAccess($this->checkAddOperationName)): ?>
        <button type="reset" class="btn btn-warning cancel">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span><?= $this->t('Cancel upload') ?></span>
        </button>
        <?php endif ?>
        <!--<button type="button" class="btn btn-danger delete">
            <i class="glyphicon glyphicon-trash"></i>
            <span>
                <?php if (!$this->checkRemoveOperationName || Yii::app()->getUser()->checkAccess($this->checkRemoveOperationName)): ?>
                    <?= $this->t('Delete') ?>
                <?php endif ?>
            </span>
        </button>
        <input type="checkbox" class="toggle">-->
        <?php endif; ?>
        <!-- The global file processing state -->
        <span class="fileupload-process"></span>
    </div>
    <!-- The global progress state -->
    <div class="col-lg-6 col-md-6 fileupload-progress fade">
        <div class="row-fluid">
            <!-- The global progress bar -->
            <div class="col-lg-6 col-md-6 progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="padding-left: 0;">
                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
            </div>
            <!-- The extended global progress state -->
            <div class="col-lg-6 col-md-6 progress-extended">&nbsp;</div>
        </div>
    </div>
</div>
<!-- The table listing the files available for upload/download -->
<table role="presentation" class="table table-striped">
    <thead>
        <tr>
            <th><?= $this->t('Thumbnail') ?></th>
            <th><?= $this->t('File name') ?></th>
            <th><?= $this->t('Upload date') ?></th>
            <th><?= $this->t('Size') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody class="files"></tbody>
</table>
<?php if ($this->showForm) echo CHtml::endForm(); ?>
