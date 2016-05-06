<tr>
    <td align="right" colspan="<?=$colspan?>">        
        <ul class="pagination">
            <?php if($prev > 0){ ?>
                <li class="paginate_button previous disabled">
                    <a href="<?=$this->Scaffold->pageLink($prev)?>">
                        <i class="fa fa-angle-left"></i>
                    </a>
                </li>
            <?php } ?>

            <?php for($page = 1;$page <= $max;$page++){ ?>
                <li class="paginate_button <?php if($page === $current){ ?> active <?php } ?>">
                    <a href="<?=$this->Scaffold->pageLink($page)?>"><?=$page?></a>
                </li>
            <?php } ?>

            <?php if($next <= $max){ ?>
                <li class="paginate_button next">
                    <a href="<?=$this->Scaffold->pageLink($next)?>">
                        <i class="fa fa-angle-right"></i>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </td>
</tr>