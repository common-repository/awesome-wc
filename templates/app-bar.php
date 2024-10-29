<?php defined('ABSPATH') or exit; 

// get bundle information
if( empty( $bundle) ) {
    $bundle = null;
    if( STAwesome()->license->isLicenseInstalled() ) {
        $bundle = STAwesome()->license->getLicensePlanData();
        
        // check bundle license status 
        // get license data
        $data = STAwesome()->api->getPlanData( $bundle->licenseCode );

        // save license data
        if( isset( $data ) && !empty( $data ) ) {
            $obj = json_decode( $data );
            STAwesome()->license->setPlanData( $obj );
        }

        $bundle = STAwesome()->license->getLicensePlanData();
    }
}

?>
<v-app-bar flat dark>
    <img src='<?php echo STAwesome()->getBase(); ?>assets/img/icon.png' />
    <v-toolbar-title>{{ pluginName }}</v-toolbar-title>

    <v-spacer></v-spacer>

    <?php if( empty( $bundle ) || $bundle->expired ) : ?>
    <v-btn rounded depressed color="#FF7052" @click="planDialog = true"><v-icon left>mdi-plus</v-icon> Activate Plan</v-btn>
    <?php else : ?>
    <div>
        <v-list-item two-line dense class="px-0" style="background-color: #616161;border-radius:5px;">
            <v-list-item-content class="py-1 px-2">
                <v-list-item-title class="body-2 green--text"><?php echo $bundle->productName; ?> </v-list-item-title>
                <?php if( $bundle->expired ) : ?>
                    <v-list-item-subtitle class="caption red--text text--lighten-2">EXPIRED</v-list-item-subtitle>
                <?php else: ?>
                    <v-list-item-subtitle class="caption">Valid until: <?php echo date("M d, Y", strtotime( $bundle->expirationDate ) ); ?></v-list-item-subtitle>
                <?php endif; ?>
            </v-list-item-content>
            <?php if( STAwesome()->license->isAboutToExpire() ) : ?>
            <v-list-item-action class="mr-1">
                <v-icon color="warning">mdi-alert-circle-outline</v-icon>
            </v-list-item-action>
            <?php endif; ?>
        </v-list-item>
    </div>
    <?php endif; ?>
</v-app-bar>

<?php if( !empty( $bundle ) && ( filter_var( $bundle->expired, FILTER_VALIDATE_BOOLEAN ) || $bundle->status === StWcLicenseManager::STATUS_EXPIRED ) ) : ?>
<div class="pa-5 pb-0">
    <v-alert type="error" text dense border="left">Your plan license has expired! Click here to <a href="https://awesomeplugin.com/pricing/" target="_blank">renew</a>.</v-alert>
</div>
<?php endif; 

if( STAwesome()->license->isAboutToExpire() ) : 
    $days = strtotime( $bundle->expirationDate ) - time();
    $days = round( $days / ( 60 * 60 * 24 ) );
    $days = $days == 1 ? "$days day" : "$days days";
?>
<div class="pa-5 pb-0">
    <v-alert type="warning" text dense border="left">Your plan license will expire in <strong><?php echo $days; ?>.</strong></v-alert>
</div>
<?php endif; ?>