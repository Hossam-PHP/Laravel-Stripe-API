<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Session;
use Stripe\Stripe;
use Stripe\Charge;

class ProductController extends Controller
{
    /**
     * @var successResponse
     * @var failResponse
     * @var createResponse
     * @var notFoundResponse
     * @var errorResponse
     */
    protected $successResponse,$failResponse,$createResponse,$notFoundResponse,$errorResponse;

    /**
     * AuthController constructor.
     *
     */
    public function __construct () {
        $this->successResponse = 200;
        $this->createResponse = 201;
        $this->notFoundResponse = 404;
        $this->failResponse = 400;
        $this->errorResponse = 500;
    }

    public function getIndex()
    {   $products = Product::all();
        return response([
            'status' => true,
            'products' => $products
        ], $this->successResponse);
    }

    public function  getAddToCart(Request $request, $id)
    {
        $product = Product::find($id);

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart ->add($product, $product->id);

        $request->session()->put('cart', $cart);
        return response([
            'status' => true,
            'cart' => Session::get('cart')
        ], $this->successResponse);
    }

    public function getReduceByOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }

        return response([
            'status' => true,
            'cart' => Session::get('cart')
        ], $this->successResponse);
    }

    public function getRemoveItem($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }


        return response([
            'status' => true,
            'cart' => Session::get('cart')
        ], $this->successResponse);
    }

    public function getCart()
    {
        if (!Session::has('cart')) {
            return response()->json([
                'status' => false,
                'message' => "There is nothing yet in your basket. Buy something amazing!",
            ], $this->failResponse);
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);

        return response([
            'status' => true,
            'products' => $cart->items,
            'totalPrice' => $cart->totalPrice
        ], $this->successResponse);
    }

    public function getCheckout()
    {
        if (!Session::has('cart')) {
            return response()->json([
                'status' => false,
                'message' => "There is nothing yet in your basket. Buy something amazing!",
            ], $this->failResponse);
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $total = $cart->totalPrice;
        return response([
            'status' => true,
            'total' => $total,
        ], $this->successResponse);
    }

    public function postCheckout(Request $request)
    {
        if (!Session::has('cart')) {
            return response()->json([
                'status' => false,
                'message' => "There is nothing yet in your basket. Buy something amazing!",
            ], $this->failResponse);
            
        }

        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);

        Stripe::setApiKey('sk_test_F6xPmo6qUdfYWe6H5mzmql9w');

        try {
            $charge = Charge::create(array(
                "amount" => $cart->totalPrice * 100,
                "currency" => "pln",
                "source" => $request->stripeToken, // obtained with Stripe.js
                "description" => "Test charge for products"
            ));

            $order = new Order();
            $order->cart = serialize($cart);
            $order->address = $request->address;
            $order->name = $request->name;
            $order->payment_id = $charge->id;

            Auth::user()->orders()->save($order);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], $this->failResponse);
        }

        Session::forget('cart');
        return response()->json([
                'status' => false,
                'message' => "Your payment is confirmed. Thank you for shopping!",
                'cart' => Session::get('cart')
            ], $this->failResponse);
    }
}
