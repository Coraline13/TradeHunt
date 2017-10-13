<?php
$lcnt = 1;
foreach ($listings as $listing) {
    /** @var Listing $listing */
    $image = $listing->getMainImageURL();
    $slug = $listing->getSlug();
    $title = $listing->getTitle();
    $tags = implode(", ", array_map(function (Tag $tag) {
        return $tag->getName();
    }, $listing->getTags()));

    $profile = $listing->getUser()->getProfile();
    $name = $profile->getFirstName().' '.$profile->getLastName();
    $added = strftime("%x", $listing->getAdded()->getTimestamp());

    echo '<div class="col-md-4 col-sm-6 listing-item">';
    echo "    <a href=\"#listingModal$lcnt\" class=\"listing-link\" data-toggle=\"modal\">";
    echo '        <div class="listing-hover"><div class="listing-hover-content">';
    echo '            <i class="fa fa-plus fa-3x"></i>';
    echo '        </div></div>';
    echo "        <img src=\"$image\" class=\"img-responsive\" alt=\"$slug\">";
    echo '    </a>';
    echo '    <div class="listing-caption">';
    echo "        <h4>$title</h4>";
    echo "        <p class=\"text-muted\">$name | $added</p>";
    echo '    </div>';
    echo '</div>';
    $lcnt += 1;
}

if (empty($listings)) {
    echo "<h2 class=\"no-results\">"._t('u', STRING_NO_RESULTS)."</h2>";
}
