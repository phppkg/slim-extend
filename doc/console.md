# console application

the console application base on Symfony/Console

## usage

a example command class, see `SlimExt\buildIn\commands\AssetPublishCommand`

## use color style

### use built in

built in color tag:

```
info comment question error
```
 
usage: `<info>Operation successful!</info>`

if you want to use more built in style, please create style instance.

```
use Symfony\Component\Console\Style\SymfonyStyle

$style = new SymfonyStyle($input, $output);

// $style->success($message);
// ...
```

### create style

```
// in command class
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

// ...
$style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
$output->getFormatter()->setStyle('fire', $style);

$output->writeln('<fire>foo</fire>');
```

> Available foreground and background colors are: black, red, green, yellow, blue, magenta, cyan and white.

> And available options are: bold, underscore, blink, reverse

### directly set colors and options 
    
You can also set these colors and options directly inside the tagname:    
    
    // green text
    $output->writeln('<fg=green>foo</>');
    
    // black text on a cyan background
    $output->writeln('<fg=black;bg=cyan>foo</>');
    
    // bold text on a yellow background
    $output->writeln('<bg=yellow;options=bold>foo</>');
