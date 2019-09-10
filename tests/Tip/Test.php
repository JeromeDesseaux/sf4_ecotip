<?php

namespace App\Tests\Tip;

use App\Entity\Tip;
use App\Repository\TipRepository;
use GetTipListTest;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
	public function test_get_tips()
	{
		$tip = new Tip();

		$postRepository = $this->prophesize(TipRepository::class);
		$postRepository->getAll()->shouldBeCalled()->willReturn([$tip]);
	}
}
