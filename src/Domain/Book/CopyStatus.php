<?php
namespace App\Domain\Book;


enum CopyStatus: string { case AVAILABLE='AVAILABLE'; case ON_LOAN='ON_LOAN'; case RESERVED='RESERVED'; case LOST='LOST'; }
