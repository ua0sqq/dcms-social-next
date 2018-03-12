<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	GIFDecoder Version 2.0 by László Zsidi, http://gifs.hu
::
::	Created at 2007. 02. 01. '07.47.AM'
::
::
::
::
::  Try on-line GIFBuilder Form demo based on GIFDecoder.
::
::  http://phpclasses.gifs.hu/demos/GifBuilder/
::
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/
class GIFDecoder
{
    public $GIF_buffer = array( );
    public $GIF_arrays = array( );
    public $GIF_delays = array( );
    public $GIF_offset = array( );
    public $GIF_stream = "";
    public $GIF_string = "";
    public $GIF_bfseek =  0;
    public $GIF_screen = array( );
    public $GIF_global = array( );
    public $GIF_sorted;
    public $GIF_colorS;
    public $GIF_colorC;
    public $GIF_colorF;
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFDecoder ( $GIF_pointer )
    ::
    */
    public function __construct($GIF_pointer)
    {
        $this->GIF_stream = $GIF_pointer;
        GIFDecoder::GIFGetByte(6);	// GIF89a
        GIFDecoder::GIFGetByte(7);	// Logical Screen Descriptor
        $this->GIF_screen = $this->GIF_buffer;
        $this->GIF_colorF = $this->GIF_buffer [ 4 ] & 0x80 ? 1 : 0;
        $this->GIF_sorted = $this->GIF_buffer [ 4 ] & 0x08 ? 1 : 0;
        $this->GIF_colorC = $this->GIF_buffer [ 4 ] & 0x07;
        $this->GIF_colorS = 2 << $this->GIF_colorC;
        if ($this->GIF_colorF == 1) {
            GIFDecoder::GIFGetByte(3 * $this->GIF_colorS);
            $this->GIF_global = $this->GIF_buffer;
        }
        /*
         *
         *  05.06.2007.
         *  Made a little modification
         *
         *
         -	for ( $cycle = 1; $cycle; ) {
         +		if ( GIFDecoder::GIFGetByte ( 1 ) ) {
         -			switch ( $this->GIF_buffer [ 0 ] ) {
         -				case 0x21:
         -					GIFDecoder::GIFReadExtensions ( );
         -					break;
         -				case 0x2C:
         -					GIFDecoder::GIFReadDescriptor ( );
         -					break;
         -				case 0x3B:
         -					$cycle = 0;
         -					break;
         -		  	}
         -		}
         +		else {
         +			$cycle = 0;
         +		}
         -	}
        */
        for ($cycle = 1; $cycle;) {
            if (GIFDecoder::GIFGetByte(1)) {
                switch ($this->GIF_buffer [ 0 ]) {
                    case 0x21:
                        GIFDecoder::GIFReadExtensions();
                        break;
                    case 0x2C:
                        GIFDecoder::GIFReadDescriptor();
                        break;
                    case 0x3B:
                        $cycle = 0;
                        break;
                }
            } else {
                $cycle = 0;
            }
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFReadExtension ( )
    ::
    */
    public function GIFReadExtensions()
    {
        GIFDecoder::GIFGetByte(1);
        for (; ;) {
            GIFDecoder::GIFGetByte(1);
            if (($u = $this->GIF_buffer [ 0 ]) == 0x00) {
                break;
            }
            GIFDecoder::GIFGetByte($u);
            /*
             * 07.05.2007.
             * Implemented a new line for a new function
             * to determine the originaly delays between
             * frames.
             *
             */
            if ($u == 4) {
                $this->GIF_delays [ ] = ($this->GIF_buffer [ 1 ] | $this->GIF_buffer [ 2 ] << 8);
            }
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFReadExtension ( )
    ::
    */
    public function GIFReadDescriptor()
    {
        $GIF_screen	= array( );
        GIFDecoder::GIFGetByte(9);
        $GIF_screen = $this->GIF_buffer;
        /*
         * 11.19.2007.
         * Implemented a new line for a new function
         * to determine the originaly XY offsets between
         * frames.
         *
         */
        $this->GIF_offset [ ] = array( ($this->GIF_buffer [ 0 ] | $this->GIF_buffer [ 1 ] << 8), ($this->GIF_buffer [ 2 ] | $this->GIF_buffer [ 3 ] << 8) );
        $GIF_colorF = $this->GIF_buffer [ 8 ] & 0x80 ? 1 : 0;
        if ($GIF_colorF) {
            $GIF_code = $this->GIF_buffer [ 8 ] & 0x07;
            $GIF_sort = $this->GIF_buffer [ 8 ] & 0x20 ? 1 : 0;
        } else {
            $GIF_code = $this->GIF_colorC;
            $GIF_sort = $this->GIF_sorted;
        }
        $GIF_size = 2 << $GIF_code;
        $this->GIF_screen [ 4 ] &= 0x70;
        $this->GIF_screen [ 4 ] |= 0x80;
        $this->GIF_screen [ 4 ] |= $GIF_code;
        if ($GIF_sort) {
            $this->GIF_screen [ 4 ] |= 0x08;
        }
        $this->GIF_string = "GIF87a";
        GIFDecoder::GIFPutByte($this->GIF_screen);
        if ($GIF_colorF == 1) {
            GIFDecoder::GIFGetByte(3 * $GIF_size);
            GIFDecoder::GIFPutByte($this->GIF_buffer);
        } else {
            GIFDecoder::GIFPutByte($this->GIF_global);
        }
        $this->GIF_string .= chr(0x2C);
        $GIF_screen [ 8 ] &= 0x40;
        GIFDecoder::GIFPutByte($GIF_screen);
        GIFDecoder::GIFGetByte(1);
        GIFDecoder::GIFPutByte($this->GIF_buffer);
        for (; ;) {
            GIFDecoder::GIFGetByte(1);
            GIFDecoder::GIFPutByte($this->GIF_buffer);
            if (($u = $this->GIF_buffer [ 0 ]) == 0x00) {
                break;
            }
            GIFDecoder::GIFGetByte($u);
            GIFDecoder::GIFPutByte($this->GIF_buffer);
        }
        $this->GIF_string .= chr(0x3B);
        /*
         *
         * Add frames into $GIF_stream array...
         *
         */
        $this->GIF_arrays [ ] = $this->GIF_string;
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetByte ( $len )
    ::
    */
    /*
     *
     *  05.06.2007.
     *  Made a little modification
     *
     *
     -	function GIFGetByte ( $len ) {
     -		$this->GIF_buffer = Array ( );
     -
     -		for ( $i = 0; $i < $len; $i++ ) {
     +			if ( $this->GIF_bfseek > strlen ( $this->GIF_stream ) ) {
     +				return 0;
     +			}
     -			$this->GIF_buffer [ ] = ord ( $this->GIF_stream { $this->GIF_bfseek++ } );
     -		}
     +		return 1;
     -	}
     */
    public function GIFGetByte($len)
    {
        $this->GIF_buffer = array( );
        for ($i = 0; $i < $len; $i++) {
            if ($this->GIF_bfseek > strlen($this->GIF_stream)) {
                return 0;
            }
            $this->GIF_buffer [ ] = ord($this->GIF_stream { $this->GIF_bfseek++ });
        }
        return 1;
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFPutByte ( $bytes )
    ::
    */
    public function GIFPutByte($bytes)
    {
        for ($i = 0; $i < count($bytes); $i++) {
            $this->GIF_string .= chr($bytes [ $i ]);
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	PUBLIC FUNCTIONS
    ::
    ::
    ::	GIFGetFrames ( )
    ::
    */
    public function GIFGetFrames()
    {
        return ($this->GIF_arrays);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetDelays ( )
    ::
    */
    public function GIFGetDelays()
    {
        return ($this->GIF_delays);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetOffset ( )
    ::
    */
    public function GIFGetOffset()
    {
        return ($this->GIF_offset);
    }
}
