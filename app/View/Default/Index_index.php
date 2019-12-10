  <?php $this->view->load('header')?>
    变量：<?php echo $name?>
    条件：
    <?php if($name){?>
        <p>is true</p>
        <else/>
        <p>is false</p>
    <?php }?>
<?php foreach($lists as $key => $item){?>
        <p><?php echo $key?> => <?php echo $item?></p>
<?php }?>

    <p>常量：<?php echo ENVIRONMENT?></p>
    <p>变量支持函数： <?php echo date('Y-m-d H:i:s', $now)?> </p>
    <?php $this->view->load('footer')?>