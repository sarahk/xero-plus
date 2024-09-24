<?php

function sum($a, $b): float
{
    return $a + $b;
}

describe('sum', function () {
    it('may sum integers', function () {
        $result = sum(1, 2);

        expect($result)->toBe(3);
    });

    it('may sum floats', function () {
        $result = sum(1.5, 2.5);

        expect($result)->toBe(4.0);
    });
});

/*
describe('bad debt image', function () {
    it('returns a url', function () {
        $url = 'https://ckm:8825/run.php?endpoint=endpoint=image&imageType=baddebt&contract_id=191';
        $result = file_get_contents($url);
        fn($result) => $result->dd();
        expect($result)->dd()->toBeUrl();

    });
});
*/
