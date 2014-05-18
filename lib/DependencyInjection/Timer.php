<?php
namespace Sea\DependencyInjection;

/**
 * A timer to get investigate the performance of your website in depth
 *
 * @author Sebastiaan Marynissen <Sebastiaan.Marynissen@UGent.be>
 */
class Timer {
    
    /**
     * The start timestamp
     * 
     * @var int
     */
    protected $start;
    
    /**
     * An array of microtime timestamps
     *
     * @var int[]
     */
    protected $stamps;
    
    /**
     * Starts the timer
     */
    public function start() {
        $this->start = microtime(true);
        return true;
    }
    
    /**
     * Sets an interval
     * 
     * @param string $text 
     */
    public function interval($text = null) {
        if (!$this->start) {
            die('Timer was not started yet!');
        }
        if (!$text) {
            $text = '*INTERVAL_' . sizeof($this->stamps) . '*';
        }
        $this->stamps[$text] = microtime(true) - $this->start;
        return $this;
    }
    
    /**
     * Stops the timer and returns the report
     * 
     * @return string
     */
    public function stop() {
        $this->stamps['*STOPPED*'] = microtime(true) - $this->start;
        $report = 'Timer report: ' . PHP_EOL . PHP_EOL;
        $report.= 'Started at: ' . $this->start . PHP_EOL;
        foreach ($this->stamps as $name => $stamp) {
            $report .= $name . ': ' . $stamp . PHP_EOL;
        }
        $this->start = null;
        return $report;
    }
    
}
