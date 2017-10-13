<?php
$lcnt = 1;
global $_USER;
foreach ($listings as $listing): ?>
<div class="listing-modal modal fade" id="listingModal<?php echo $lcnt ?>" tabindex="-1" role="dialog">
    <div class="modal-content">
        <div class="close-modal" data-dismiss="modal">
            <div class="lr">
                <div class="rl">
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <div class="modal-body">
                        <?php
                        /** @var Listing $listing */
                        $user = $listing->getUser();
                        $profile = $user->getProfile();
                        $name = $profile->getFirstName().' '.$profile->getLastName();
                        $added = strftime("%x", $listing->getAdded()->getTimestamp())
                        ?>
                        <!-- Project Details Go Here -->
                        <h2 class="<?php echo ($listing->getType() == Listing::TYPE_OFFER ? "offer" : "wish") ?>"><?php echo $listing->getTitle() ?></h2>
                        <p class="item-intro text-muted"><?php echo $name.' | '.$added ?></p>
                        <img class="img-responsive" src="<?php echo $listing->getMainImageURL() ?>"
                             alt="<?php echo $listing->getSlug() ?>">
                        <p><?php echo nl2br($listing->getDescription()) ?></p>
                        <?php if (!empty($_USER) && $user->getId() != $_USER->getId()): ?>
                        <a class="btn btn-primary" href="trade.php?user_id=<?php echo $user->getId() ?>&prechecked_id=<?php echo $listing->getId() ?>">
                            <i class="fa fa-times"></i>
                            <?php echo _t('u', STRING_TRADE_WITH_USER, $name) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$lcnt += 1;
endforeach; ?>

