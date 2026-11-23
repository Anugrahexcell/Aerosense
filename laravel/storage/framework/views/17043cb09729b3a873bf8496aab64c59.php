<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildSchema()); ?>

</div>
<?php /**PATH C:\Users\ega\.gemini\antigravity\scratch\aerosense-V2\vendor\filament\schemas\resources\views/components/grid.blade.php ENDPATH**/ ?>