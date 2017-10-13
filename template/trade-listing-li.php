<li>
    <label for="listing-<?php echo $listing->getId() ?>">
        <input id="listing-<?php echo $listing->getId() ?>" type="checkbox" name="listing_ids[]"
               value="<?php echo $listing->getId() ?>" <?php echo in_array($listing->getId(), $listing_ids) || $listing->getId() == $prechecked_id ? "checked" : "" ?>/>
        <img src="<?php echo $listing->getMainImageURL() ?>" alt="<?php echo $listing->getSlug() ?>"/>
        <span><?php echo $listing->getTitle() ?></span>
    </label>
</li>
